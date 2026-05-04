<?php

declare(strict_types=1);

// MODE: 'test' ou 'live'
const STRIPE_MODE = 'test';

// CLÉS TEST (pour développement)
const STRIPE_TEST_PUBLIC_KEY = 'pk_test_51QX2gR2KklZhvSp1xQx6yR8Xz9P2qW1E';
const STRIPE_TEST_SECRET_KEY = 'sk_test_51QX2gR2KklZhvSp16uK7L8M9N0O1P2Q3';

// CLÉS LIVE (pour production)
const STRIPE_LIVE_PUBLIC_KEY = 'pk_live_YOUR_LIVE_PUBLIC_KEY_HERE';
const STRIPE_LIVE_SECRET_KEY = 'sk_live_YOUR_LIVE_SECRET_KEY_HERE';

function getStripePublicKey(): string
{
    return STRIPE_MODE === 'live' 
        ? STRIPE_LIVE_PUBLIC_KEY 
        : STRIPE_TEST_PUBLIC_KEY;
}

function getStripeSecretKey(): string
{
    return STRIPE_MODE === 'live' 
        ? STRIPE_LIVE_SECRET_KEY 
        : STRIPE_TEST_SECRET_KEY;
}

function validateStripeKeys(): bool
{
    $public = getStripePublicKey();
    $secret = getStripeSecretKey();
    return str_starts_with($public, 'pk_') && str_starts_with($secret, 'sk_');
}