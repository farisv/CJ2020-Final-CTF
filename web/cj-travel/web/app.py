import os
import html
import re
import requests
from flask import Flask, Response, flash, render_template, request, session, redirect, url_for, abort
from werkzeug.security import generate_password_hash, check_password_hash
from flaskext.mysql import MySQL
from flask_caching import Cache
from pymysql import Error, IntegrityError

app = Flask(__name__)
app.secret_key = os.urandom(16)
app.config['MYSQL_DATABASE_HOST'] = 'db'
app.config['MYSQL_DATABASE_DB'] = 'cj_travel'
app.config['MYSQL_DATABASE_USER'] = 'cj_user'
app.config['MYSQL_DATABASE_PASSWORD'] = 'cj_password'

mysql = MySQL()
mysql.init_app(app)

cache = Cache(config={'CACHE_TYPE': 'simple'})
cache.init_app(app)

email_regex = re.compile(r"[^@\s]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+")

@app.template_filter()
def currency(value):
    value = float(value)
    return 'Rp {:,.2f}'.format(value)

@app.route('/')
def index():
    hotels = cache.get('hotels')
    if hotels is None:
        connection = mysql.connect()
        try:
            with connection.cursor() as cursor:
                query = 'SELECT id, name, price, description FROM hotels'
                cursor.execute(query)
                hotels = cursor.fetchall()
        finally:
            connection.close()
        cache.set('hotels', hotels)
    return render_template('index.html', hotels=hotels)

@app.route('/register', methods=['GET', 'POST'])
def register():
    if session.get('user', None):
        return redirect(url_for('index'))

    if request.method == 'GET':
        return render_template('register.html')

    email = request.form.get('email', None)
    password = request.form.get('password', None)
    name = request.form.get('name', None)
    if not email or not password or not name:
        return render_template('register.html')

    if not email_regex.fullmatch(email) or 'fc6ea87d8348147f2070ff8529482136' in email:
        flash('Invalid email address', 'danger')
        return render_template('register.html')

    hashed_password = generate_password_hash(password)
    connection = mysql.connect()
    try:
        with connection.cursor() as cursor:
            query = 'INSERT INTO users (email, password, name) VALUES (%s, %s, %s)'
            cursor.execute(query, (email, hashed_password, html.escape(name)))
            connection.commit()
        flash('Successfully registered', 'success')
        return redirect(url_for('login'))

    except IntegrityError:
        flash('Email is already registered', 'danger')
    finally:
        connection.close()
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if session.get('user', None):
        return redirect(url_for('index'))

    if request.method == 'GET':
        return render_template('login.html')

    email = request.form.get('email', None)
    password = request.form.get('password', None)
    if not email or not password:
        return render_template('index.html')

    connection = mysql.connect()
    try:
        with connection.cursor() as cursor:
            query = 'SELECT id, password, name FROM users WHERE email = %s'
            count = cursor.execute(query, email)
            if count == 0:
                raise AssertionError

            user_id, hashed_password, name = cursor.fetchone()
            if not check_password_hash(hashed_password, password):
                raise AssertionError

            session['user'] = {'id': user_id}
            return redirect(url_for('index'))

    except AssertionError:
        flash('Invalid email or password', 'danger')

    finally:
        connection.close()
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.pop('user', None)
    return redirect(url_for('index'))

@app.route('/hotels/<int:hotel_id>', methods=['GET', 'POST'])
def hotels_route(hotel_id):
    user = session.get('user', None)
    if user is None:
        return redirect(url_for('login'))

    connection = mysql.connect()
    try:
        with connection.cursor() as cursor:
            query = 'SELECT name, price, description FROM hotels WHERE id = %s'
            count = cursor.execute(query, hotel_id)
            if count == 0:
                return redirect(url_for('index'))
            hotel_name, hotel_price, hotel_description = cursor.fetchone()
    finally:
        connection.close()

    if request.method == 'GET':
        return render_template('hotel.html',
            hotel_name=hotel_name,
            hotel_price=hotel_price,
            hotel_description=hotel_description)

    user_id = user['id']
    email = request.form.get('email', None)
    phone = request.form.get('phone', None)
    guest = request.form.get('guest', None)

    if not phone or not guest:
        return redirect(url_for('index'))

    if email and (not email_regex.fullmatch(email) or 'fc6ea87d8348147f2070ff8529482136' in email):
        flash('Invalid email address', 'danger')
        return redirect(url_for('index'))

    connection = mysql.connect()
    try:
        with connection.cursor() as cursor:
            if not email:
                query = 'SELECT email FROM users WHERE id = %s'
                cursor.execute(query, user_id)
                email = cursor.fetchone()

            query = 'INSERT INTO hotel_bookings (user_id, hotel_id, email_fc6ea87d8348147f2070ff8529482136, phone, guest) VALUES (%s, %s, %s, %s, %s)'
            cursor.execute(query, (user_id, hotel_id, email, phone, guest))
            connection.commit()
        flash('Successfully booked', 'success')
    finally:
        connection.close()
    return redirect(url_for('index'))

@app.route('/bookings')
def bookings_route():
    user = session.get('user', None)
    if not user:
        return redirect(url_for('index'))

    user_id = user['id']
    connection = mysql.connect()
    try:
        with connection.cursor() as cursor:
            query = 'SELECT id, email_fc6ea87d8348147f2070ff8529482136, guest FROM hotel_bookings WHERE user_id = %s'
            cursor.execute(query, user_id)
            bookings = cursor.fetchall()
    finally:
        connection.close()
    return render_template('bookings.html', bookings=bookings)

@app.route('/bookings/eticket', methods=['POST'])
def bookings_eticket():
    user = session.get('user', None)
    if not user:
        return redirect(url_for('index'))

    booking_id = request.form.get('booking_id', None)
    if not booking_id:
        return redirect(url_for('index'))

    email = None
    user_id = user['id']
    connection = mysql.connect()
    try:
        with connection.cursor() as cursor:
            query = 'SELECT hotel_id, email_fc6ea87d8348147f2070ff8529482136, phone, guest FROM hotel_bookings WHERE id = %s AND user_id = %s'
            count = cursor.execute(query, (booking_id, user_id))
            if count == 0:
                return redirect(url_for('index'))
            hotel_id, email, phone, guest = cursor.fetchone()

        with connection.cursor() as cursor:
            query = 'SELECT name FROM users WHERE email = \'%s\'' % (email)
            count = cursor.execute(query)
            if count > 0:
                email_name, = cursor.fetchone()
                email = '%s <span>(%s)</span>' % (html.escape(email), email_name)
            else:
                email = html.escape(email)

        with connection.cursor() as cursor:
            query = 'SELECT name, price, description FROM hotels WHERE id = %s'
            cursor.execute(query, hotel_id)
            hotel_name, hotel_price, hotel_description = cursor.fetchone()
    except Error as err:
        app.logger.error('email: %s', email)
        abort(500)
    finally:
        connection.close()

    rendered = render_template('eticket.html',
        hotel_name=hotel_name,
        hotel_price=hotel_price,
        hotel_description=hotel_description,
        email=email,
        phone=phone,
        guest=guest,
    )
    data = {'html': rendered}
    res = requests.post('http://weasyprint:3000/generate_pdf',
        stream=True,
        data=data)
    headers = dict(res.raw.headers)
    def generate():
        for chunk in res.raw.stream(decode_content=False):
            yield chunk
    return Response(generate(), status=res.status_code, headers=headers)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=3000, debug=False)
