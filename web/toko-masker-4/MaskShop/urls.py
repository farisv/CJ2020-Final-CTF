from django.urls import include, path

urlpatterns = [
    path('', include('MaskShop.shop.urls')),
]
