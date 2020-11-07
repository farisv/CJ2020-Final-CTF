import os
import io
import urllib.request
from flask import Flask, Response, request, send_file, abort
from weasyprint import HTML

app = Flask(__name__)
app.secret_key = os.urandom(16)

@app.route('/generate_pdf_from_url', methods=['GET'])
def generate_pdf_from_url():
    url = request.args.get('url', None)
    if not url:
        return abort(400)

    with urllib.request.urlopen(url, timeout=3.0) as res:
        data = res.read()
    html_string = data.decode('utf8')
    html = HTML(string=html_string)
    pdf = html.write_pdf()
    return send_file(io.BytesIO(pdf), mimetype='application/pdf')

@app.route('/generate_pdf', methods=['POST'])
def generate_pdf():
    html_string = request.form.get('html', None)
    if not html_string:
        return abort(400)

    html = HTML(string=html_string)
    pdf = html.write_pdf()
    return send_file(io.BytesIO(pdf), mimetype='application/pdf')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=3000, debug=False)
