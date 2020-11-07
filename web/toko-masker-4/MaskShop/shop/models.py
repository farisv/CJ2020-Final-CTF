from django.db import models


class Item(models.Model):
    name = models.CharField(max_length=128)
    price = models.IntegerField()
    quantity = models.IntegerField()
    image_path = models.CharField(max_length=128)
