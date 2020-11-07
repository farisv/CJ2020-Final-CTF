from django.core.management.base import BaseCommand
from MaskShop.shop.models import Item

class Command(BaseCommand):
    args = ''
    help = 'Populate DB'

    def _populate_db(self):
        f1 = Item(name="Surgical Mask", price=10, quantity=30000, image_path='surgical_mask.jpg')
        f2 = Item(name="N95 Mask", price=80, quantity=20000, image_path='n95_mask.jpg')
        f3 = Item(name="N99 Mask", price=100, quantity=10000, image_path='n99_mask.jpg')
        f4 = Item(name="Gas Mask", price=500, quantity=300, image_path='gas_mask.jpg')
        f1.save()
        f2.save()
        f3.save()
        f4.save()

    def handle(self, *args, **options):
        self._populate_db()
