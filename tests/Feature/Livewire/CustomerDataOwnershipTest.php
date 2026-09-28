<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Nezasa\Checkout\Actions\TripDetails\CallTripDetailsAction;
use Nezasa\Checkout\Dtos\Checkout\CheckoutOwnerDto;
use Nezasa\Checkout\Dtos\Checkout\CheckoutParamsDto;
use Nezasa\Checkout\Livewire\ContactDetails;
use Nezasa\Checkout\Livewire\TripDetailsPage;
use Nezasa\Checkout\Models\Checkout;
use Nezasa\Checkout\Models\Transaction;
use Nezasa\Checkout\Payments\Enums\TransactionStatusEnum;

/**
 * @return array<string, string|bool>
 */
function ownershipQueryParams(): array
{
    return [
        'itineraryId' => 'itinerary-123',
        'checkoutId' => 'checkout-456',
        'origin' => 'APP',
        'lang' => 'en',
        'rest-payment' => false,
    ];
}

function ownershipCheckout(?string $ownerKey = null): Checkout
{
    return Checkout::create([
        'checkout_id' => 'checkout-456',
        'itinerary_id' => 'itinerary-123',
        'origin' => 'APP',
        'lang' => 'en',
        'data' => [
            'owner' => $ownerKey ? CheckoutOwnerDto::issue($ownerKey, 60)->toArray() : null,
            'contact' => ['firstName' => 'Johnathan', 'lastName' => 'Stored', 'email' => 'johnathan@example.com'],
            'paxInfo' => [],
            'activityAnswers' => [],
            'acceptedTerms' => [],
            'numberOfPax' => 1,
            'status' => Checkout::buildSectionStatus(),
            'insurance' => null,
        ],
    ]);
}

/**
 * @return array<string, mixed>
 */
function contactDetailsProps(Checkout $checkout): array
{
    $responses = resolve(CallTripDetailsAction::class)
        ->run(new CheckoutParamsDto('checkout-456', 'itinerary-123', 'APP', 'en'));

    return [
        'model' => $checkout,
        'checkoutId' => $checkout->checkout_id,
        'itineraryId' => $checkout->itinerary_id,
        'origin' => $checkout->origin,
        'lang' => $checkout->lang,
        'contactRequirements' => $responses->travelerRequirements->contact,
        'countryCodes' => $responses->countryCodes,
        'countriesResponse' => $responses->countries,
    ];
}

beforeEach(function (): void {
    Config::set('checkout.customer_data.restore_always', false);

    fakeInitialNezasaCalls();
});

it('shows another browser a neutral page while the owner is entering the data', function (): void {
    $checkout = ownershipCheckout('owner-key');

    $this->get(route('traveler-details', ownershipQueryParams()))
        ->assertStatus(409)
        ->assertSee('This checkout is open in another browser. Please try again later.')
        ->assertDontSee('Johnathan');

    expect($checkout->refresh()->data->get('contact'))->toMatchArray(['firstName' => 'Johnathan'])
        ->and($checkout->getOwner()->isHeldBy('owner-key'))->toBeTrue();
});

it('lets another browser start over once the owner has been inactive for the configured minutes', function (): void {
    $checkout = ownershipCheckout('owner-key');

    $this->travel(61)->minutes();

    Livewire::withQueryParams(ownershipQueryParams())
        ->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.index')
        ->assertDontSee('Johnathan')
        ->assertDontSee('johnathan@example.com');

    $checkout->refresh();

    expect($checkout->data->get('contact'))->not->toHaveKeys(['lastName', 'email'])
        ->and($checkout->getOwner()->isHeldBy('owner-key'))->toBeFalse();
});

it('shows the stored customer data to the browser holding the key', function (): void {
    $checkout = ownershipCheckout('owner-key');

    Livewire::withQueryParams(ownershipQueryParams())
        ->withCookie('checkout_owner_'.$checkout->id, 'owner-key')
        ->test(TripDetailsPage::class)
        ->assertSee('Johnathan')
        ->assertSee('johnathan@example.com');
});

it('shows the stored customer data to everyone when restoring is always enabled', function (): void {
    Config::set('checkout.customer_data.restore_always', true);

    ownershipCheckout('owner-key');

    Livewire::withQueryParams(ownershipQueryParams())
        ->test(TripDetailsPage::class)
        ->assertSee('Johnathan');
});

it('forgets the previous customer data once a page left open after the owner expired changes the contact', function (): void {
    $checkout = ownershipCheckout('owner-key');
    $this->travel(61)->minutes();

    Livewire::test(ContactDetails::class, contactDetailsProps($checkout))
        ->assertSet('contact.firstName', null)
        ->set('contact.firstName', 'Jane');

    $checkout->refresh();

    expect($checkout->data->get('contact'))->toMatchArray(['firstName' => 'Jane'])
        ->and($checkout->data->get('contact'))->not->toHaveKeys(['lastName', 'email'])
        ->and($checkout->getOwner()->isHeldBy('owner-key'))->toBeFalse();
});

it('refuses a change from another browser while the owner is active', function (): void {
    $checkout = ownershipCheckout('owner-key');
    Livewire::test(ContactDetails::class, contactDetailsProps($checkout))
        ->set('contact.firstName', 'Jane')
        ->assertStatus(409);

    expect($checkout->refresh()->data->get('contact'))->toMatchArray(['firstName' => 'Johnathan', 'lastName' => 'Stored']);
});

it('keeps the customer data when the owner changes the contact', function (): void {
    $checkout = ownershipCheckout('owner-key');

    Livewire::withCookie('checkout_owner_'.$checkout->id, 'owner-key')
        ->test(ContactDetails::class, contactDetailsProps($checkout))
        ->assertSet('contact.firstName', 'Johnathan')
        ->set('contact.firstName', 'Jane');

    $checkout->refresh();

    expect($checkout->data->get('contact'))->toMatchArray(['firstName' => 'Jane', 'lastName' => 'Stored'])
        ->and($checkout->getOwner()->isHeldBy('owner-key'))->toBeTrue();
});

it('shows another browser a neutral page while the owner may still be paying', function (): void {
    $checkout = ownershipCheckout('owner-key');
    Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Stripe',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Pending,
    ]);
    $insurance = $checkout->data->get('insurance');

    $this->get(route('traveler-details', ownershipQueryParams()))
        ->assertStatus(409)
        ->assertSee('This checkout is open in another browser. Please try again later.')
        ->assertDontSee('Johnathan')
        ->assertDontSee('payment');

    expect($checkout->refresh()->data->get('contact'))->toMatchArray(['firstName' => 'Johnathan'])
        ->and($checkout->data->get('insurance'))->toBe($insurance);
});

it('lets the owner back into the checkout while paying', function (): void {
    $checkout = ownershipCheckout('owner-key');
    Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Stripe',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Pending,
    ]);

    Livewire::withQueryParams(ownershipQueryParams())
        ->withCookie('checkout_owner_'.$checkout->id, 'owner-key')
        ->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.index')
        ->assertSee('Johnathan');
});

it('cancels the pending transaction when the owner returns from the payment provider', function (): void {
    $checkout = ownershipCheckout('owner-key');
    $transaction = Transaction::create([
        'checkout_id' => $checkout->id,
        'gateway' => 'Stripe',
        'amount' => 100,
        'currency' => 'EUR',
        'status' => TransactionStatusEnum::Pending,
    ]);

    Livewire::withQueryParams([...ownershipQueryParams(), 'transaction' => $transaction->id])
        ->withCookie('checkout_owner_'.$checkout->id, 'owner-key')
        ->test(TripDetailsPage::class)
        ->assertViewIs('checkout::blades.index');

    expect($transaction->refresh()->status)->toBe(TransactionStatusEnum::Canceled);
});
