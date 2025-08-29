<div class="relative rounded-lg overflow-hidden mb-6">
    <img
        src="https://thumbs.dreamstime.com/b/luxury-hotel-bellagio-las-vegas-nv-june-june-usa-casino-located-36820850.jpg"
        alt="Luxury resort with pool in Palma, Majorca"
        class="w-full h-48 object-cover"
    />
    <div class="absolute bottom-0 left-0 p-6 text-white bg-gradient-to-t from-black/70 to-transparent w-full">
        <h2 class="text-xl font-semibold flex items-center">
            {{str($itinerary->title)->limit(50) }}
        </h2>
        <p>{{$itinerary->startDate->format('D, j M Y')}} - {{$itinerary->endDate->format('D, j M Y')}}</p>
    </div>
    <button class="absolute bottom-6 right-6 border border-white text-white px-4 py-2 rounded-lg bg-transparent hover:bg-white/10 transition">
        <a href="{{config('checkout.nezasa.base_url')}}/itineraries/{{$this->itineraryId}}">
            {{trans('checkout::page.trip_details.view_full_itinerary')}}
        </a>

    </button>
</div>
