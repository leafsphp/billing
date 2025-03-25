<?php

app()->post('/webhooks/billing', 'BillingWebhooksController@handle');
