from django.conf import settings
from django.urls import include, path, re_path
from django.views.static import serve
from . import api, views

urlpatterns = [
    path('', views.index),
    path('checkout', views.checkout),
    path('invoice', views.invoice),
    path('api/v1/getItemList', api.get_item_list),
    path('api/v1/getState', api.get_state),
    path('api/v1/getSelectedItems', api.get_selected_items),
    path('api/v1/getInvoice', api.get_invoice),
    re_path(r'^static/(?P<path>.*)$', serve, {
        'document_root': settings.MEDIA_ROOT,
    }),
]
