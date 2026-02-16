import ccxt
import pandas as pd
import pandas_ta as ta
import time
import threading
import logging
from crypto_bot import config

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class TradingBot:
    def __init__(self):
        self.exchange = ccxt.binance({
            'apiKey': config.API_KEY,
            'secret': config.API_SECRET,
            'enableRateLimit': True,
            'options': {'defaultType': 'spot'}
        })
        self.symbol = config.SYMBOL
        self.timeframe = config.TIMEFRAME
        self.rsi_period = config.RSI_PERIOD
        self.ema_period = config.EMA_PERIOD

        self.is_running = False
        self.thread = None
        self.last_signal = None
        self.trades = []
        self.balance = {}

    def fetch_data(self):
        try:
            ohlcv = self.exchange.fetch_ohlcv(self.symbol, self.timeframe)
            if not ohlcv:
                return None
            df = pd.DataFrame(ohlcv, columns=['timestamp', 'open', 'high', 'low', 'close', 'volume'])
            df['timestamp'] = pd.to_datetime(df['timestamp'], unit='ms')
            return df
        except Exception as e:
            logger.error(f"Error fetching data: {e}")
            return None

    def calculate_indicators(self, df):
        df['rsi'] = ta.rsi(df['close'], length=self.rsi_period)
        df['ema'] = ta.ema(df['close'], length=self.ema_period)
        return df

    def check_signals(self, df):
        if len(df) < max(self.rsi_period, self.ema_period) + 1:
            return None

        last_row = df.iloc[-1]
        prev_row = df.iloc[-2]

        rsi = last_row['rsi']
        ema = last_row['ema']
        close = last_row['close']

        prev_rsi = prev_row['rsi']

        # Strategy:
        # Buy when RSI crosses above 30 AND price > EMA
        if prev_rsi <= 30 and rsi > 30 and close > ema:
            return 'BUY'

        # Sell when RSI crosses below 70 OR price < EMA
        if (prev_rsi >= 70 and rsi < 70) or close < ema:
            return 'SELL'

        return None

    def execute_trade(self, signal):
        logger.info(f"Signal detected: {signal}")
        try:
            price = self.exchange.fetch_ticker(self.symbol)['last']

            if config.DRY_RUN or config.API_KEY == 'your_api_key_here':
                logger.info(f"[DRY RUN] Would execute {signal} at {price}")
                order = {'id': 'dry_run_id', 'price': price, 'amount': 0.0, 'status': 'closed'}
            else:
                # Real trade execution
                self.update_balance()
                base, quote = self.symbol.split('/')

                if signal == 'BUY':
                    # Buy with all available quote currency
                    quote_balance = self.balance.get(quote, 0)
                    if quote_balance > 10: # Minimum 10 USDT
                        amount = quote_balance / price
                        order = self.exchange.create_market_buy_order(self.symbol, amount)
                        logger.info(f"Executed REAL BUY order: {order['id']}")
                    else:
                        logger.warning(f"Insufficient {quote} balance for BUY")
                        return
                else: # SELL
                    # Sell all available base currency
                    base_balance = self.balance.get(base, 0)
                    if base_balance > 0.0001: # Small minimum
                        order = self.exchange.create_market_sell_order(self.symbol, base_balance)
                        logger.info(f"Executed REAL SELL order: {order['id']}")
                    else:
                        logger.warning(f"Insufficient {base} balance for SELL")
                        return

            trade = {
                'timestamp': time.strftime('%Y-%m-%d %H:%M:%S'),
                'symbol': self.symbol,
                'type': signal,
                'price': price,
                'order_id': order.get('id', 'N/A')
            }
            self.trades.append(trade)
            self.last_signal = signal

        except Exception as e:
            logger.error(f"Error executing trade: {e}")

    def update_balance(self):
        try:
            if config.API_KEY == 'your_api_key_here':
                self.balance = {'USDT': 1000.0, 'BTC': 0.0}
            else:
                balance = self.exchange.fetch_balance()
                self.balance = {k: v['free'] for k, v in balance['total'].items() if v['free'] > 0}
        except Exception as e:
            logger.error(f"Error fetching balance: {e}")
            self.balance = {'Error': 'API Error (Restricted location or invalid keys)'}

    def run_loop(self):
        while self.is_running:
            try:
                self.update_balance()
                logger.info("Bot checking for signals...")
                df = self.fetch_data()
                if df is not None:
                    df = self.calculate_indicators(df)
                    signal = self.check_signals(df)

                    if signal and signal != self.last_signal:
                        self.execute_trade(signal)

                time.sleep(config.UPDATE_INTERVAL)
            except Exception as e:
                logger.error(f"Loop error: {e}")
                time.sleep(10)

    def start(self):
        if not self.is_running:
            self.update_balance()
            self.is_running = True
            self.thread = threading.Thread(target=self.run_loop, daemon=True)
            self.thread.start()
            logger.info(f"Bot started (DRY_RUN={config.DRY_RUN}).")

    def stop(self):
        self.is_running = False
        if self.thread:
            self.thread.join(timeout=5)
        logger.info("Bot stopped.")

# Instance for shared use
bot_instance = TradingBot()
