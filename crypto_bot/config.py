import os

# Binance API Keys
API_KEY = os.getenv('BINANCE_API_KEY', 'your_api_key_here')
API_SECRET = os.getenv('BINANCE_API_SECRET', 'your_api_secret_here')

# Trading Parameters
SYMBOL = 'BTC/USDT'
TIMEFRAME = '1h'
RSI_PERIOD = 14
EMA_PERIOD = 50
DRY_RUN = True  # Set to False to execute real trades

# Bot Settings
UPDATE_INTERVAL = 60  # seconds
