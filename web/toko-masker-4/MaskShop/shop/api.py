from django.conf import settings
from django.core import serializers
from django.http import JsonResponse, HttpResponse
from .models import Item
from .helpers import encrypt, decrypt

import json

def get_item_list(request):
    items = Item.objects.order_by('id')
    items_json = serializers.serialize('json', items)
    return HttpResponse(items_json, content_type='application/json')

def get_state(request):
    json_data = json.loads(request.body)
    selected_items = json_data['selectedItems']
    total_price = 0
    if len(selected_items) == 0:
        return JsonResponse({'error' : 'No item selected'})
    for selected_item in selected_items:
        item = Item.objects.get(pk=selected_item['pk'])
        selected_item['price'] = item.price
        selected_item['image_path'] = item.image_path
        selected_item['name'] = item.name
        total_price += int(selected_item['price']) * abs(int(selected_item['quantity']))
    json_data['totalPrice'] = total_price
    state = encrypt(json.dumps(json_data))
    return JsonResponse({'state' : state})

def get_selected_items(request):
    json_data = json.loads(request.body)
    state = json_data['state']
    decrypted_state = decrypt(state)
    return HttpResponse(decrypted_state, content_type='application/json')

def get_invoice(request):
    json_data = json.loads(request.body)
    state = json_data['state']
    decrypted_state = decrypt(state)
    json_temp = json.loads(decrypted_state)
    if json_temp['totalPrice'] < 0:
        return JsonResponse({'error' : 'Can\'t create invoice'})
    json_temp['totalPrice'] = 0
    for selected_item in json_temp['selectedItems']:
        json_temp['totalPrice'] += int(selected_item['price']) * abs(int(selected_item['quantity']))
    json_temp['message'] = 'Your balance is not enough'
    if json_temp['totalPrice'] <= settings.BALANCE:
        satisfied = False
        for selected_item in json_temp['selectedItems']:
            if (int(selected_item['pk']) == 3) and (int(selected_item['quantity']) >= settings.REQUIRED):
                satisfied = True
                json_temp['message'] = settings.FLAG
        if not satisfied:
            json_temp['message'] = 'Your balance is enough, but, no flag for you'
    return HttpResponse(json.dumps(json_temp), content_type='application/json')