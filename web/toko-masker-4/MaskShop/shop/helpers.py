from django.conf import settings

from base64 import b64encode, b64decode
from Crypto.Cipher import AES

def encrypt(s):
    obj = AES.new(settings.ENC_KEY, AES.MODE_ECB, settings.ENC_KEY)
    s = s.encode('utf8')
    length = 16 - (len(s) % 16)
    s += bytes([length]) * length
    cipher_bytes = obj.encrypt(s)
    encoded_cipher_bytes = b64encode(cipher_bytes).decode('utf-8')
    return encoded_cipher_bytes

def decrypt(s):
    obj = AES.new(settings.ENC_KEY, AES.MODE_ECB, settings.ENC_KEY)
    decoded_cipher_bytes = b64decode(s.encode('utf-8'))
    decrypted_string = obj.decrypt(decoded_cipher_bytes)
    decrypted_string = decrypted_string[:-decrypted_string[-1]]
    return decrypted_string.decode('utf-8')