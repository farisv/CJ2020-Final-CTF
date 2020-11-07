from django.http import HttpResponse
from django.shortcuts import render

def index(request):
    return render(request, 'index.html')

def checkout(request):
    return render(request, 'checkout.html')

def invoice(request):
    return render(request, 'invoice.html')
