from flask import Flask, render_template, jsonify, request
from crypto_bot.bot import bot_instance
import logging

app = Flask(__name__)

# Disable Flask default logging to keep console clean
log = logging.getLogger('werkzeug')
log.setLevel(logging.ERROR)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/status')
def status():
    return jsonify({
        'is_running': bot_instance.is_running,
        'balance': bot_instance.balance,
        'trades': bot_instance.trades,
        'symbol': bot_instance.symbol,
        'last_signal': bot_instance.last_signal
    })

@app.route('/start', methods=['POST'])
def start_bot():
    bot_instance.start()
    return jsonify({'message': 'Bot started', 'status': bot_instance.is_running})

@app.route('/stop', methods=['POST'])
def stop_bot():
    bot_instance.stop()
    return jsonify({'message': 'Bot stopped', 'status': bot_instance.is_running})

if __name__ == '__main__':
    app.run(debug=True, port=5000)
