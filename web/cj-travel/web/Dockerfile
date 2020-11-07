FROM python:3-alpine

WORKDIR /app
COPY requirements.txt /app/requirements.txt
RUN pip install -r requirements.txt

USER nobody
CMD ["python3", "app.py"]
