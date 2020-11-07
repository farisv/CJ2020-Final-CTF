FROM python:3.6
ENV PYTHONUNBUFFERED 1
RUN mkdir /ctf
WORKDIR /ctf
ADD requirements.txt /ctf/
RUN pip install -r requirements.txt
ADD . /ctf
RUN useradd ctf 
RUN chown -R ctf:ctf /ctf