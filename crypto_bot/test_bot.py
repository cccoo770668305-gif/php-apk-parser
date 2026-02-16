import pytest
import pandas as pd
import numpy as np
from crypto_bot.bot import TradingBot

@pytest.fixture
def bot():
    return TradingBot()

def test_calculate_indicators(bot):
    # Create dummy data
    data = {
        'close': np.random.uniform(100, 200, 100)
    }
    df = pd.DataFrame(data)
    df = bot.calculate_indicators(df)

    assert 'rsi' in df.columns
    assert 'ema' in df.columns
    assert not df['rsi'].isnull().all()
    assert not df['ema'].isnull().all()

def test_check_signals_buy(bot):
    # RSI crosses above 30 and price > EMA
    # We need at least max(rsi_period, ema_period) + 1 rows
    # Default periods are 14 and 50. Let's use 60 rows.

    # Create a scenario for BUY signal
    # Prev RSI <= 30, Current RSI > 30, Price > EMA
    data = {
        'close': [100] * 58 + [90, 110] # Price goes up at the end
    }
    df = pd.DataFrame(data)

    # Manually set RSI and EMA for testing signal logic
    df['rsi'] = [25] * 59 + [35]
    df['ema'] = [100] * 60

    signal = bot.check_signals(df)
    assert signal == 'BUY'

def test_check_signals_sell_rsi(bot):
    # RSI crosses below 70
    data = {
        'close': [100] * 60
    }
    df = pd.DataFrame(data)
    df['rsi'] = [75] * 59 + [65]
    df['ema'] = [50] * 60

    signal = bot.check_signals(df)
    assert signal == 'SELL'

def test_check_signals_sell_ema(bot):
    # Price < EMA
    data = {
        'close': [100] * 59 + [40]
    }
    df = pd.DataFrame(data)
    df['rsi'] = [50] * 60
    df['ema'] = [50] * 60

    signal = bot.check_signals(df)
    assert signal == 'SELL'
