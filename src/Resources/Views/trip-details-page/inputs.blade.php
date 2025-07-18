@php($inputs = 0)
@php($openTag = false)

@foreach($requirements as $name => $value)
    @if($value->isHidden())
        @continue
    @endif

    @if($inputs === 0 && !$openTag)
        @php($openTag = true)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 min-w-0 mb-4">
            @endif

            @if(! in_array($name, ['address1', 'address2', 'gender', 'mobilePhone', 'country', 'nationality', 'passportIssuingCountry']))
                @include('checkout::components.input', [
                    'label' => $name,
                    'wireModel' => "$saveTo.$name",
                    'placeholder' => $name,
                ])
                @php($inputs++)
            @endif


            @switch($name)
                @case('gender')
                    @include('checkout::components.gender', ['wireModel' => "$saveTo.$name"])
                    @php($inputs++)
                    @break

                @case('mobilePhone')
                    @include('checkout::components.phone', ['wireModel' => "$saveTo.$name", 'codes' => $countryCodes])
                    @php($inputs++)
                    @break

                @case('country')
                    @include('checkout::components.country', [
                            'name' => $name,
                            'wireModel' => "$saveTo.$name",
                            'countriesResponse' => $countriesResponse
                    ])
                    @php($inputs++)
                    @break

                @case('nationality')
                    @include('checkout::components.country', [
                            'name' => $name,
                            'wireModel' => "$saveTo.$name",
                            'countriesResponse' => $countriesResponse
                    ])
                    @php($inputs++)
                    @break

                @case('passportIssuingCountry')
                    @include('checkout::components.country', [
                            'label' => $name,
                            'wireModel' => "$saveTo.$name",
                            'countriesResponse' => $countriesResponse
                    ])
                    @php($inputs++)
                    @break

            @endswitch


            @if($inputs === 3 && $openTag)
        </div>
        @php($inputs = 0)
        @php($openTag = false)
        @endif

        @endforeach

        @if($openTag)
            </div>
    @endif


    @unless($requirements->address1->isHidden())

            @include('checkout::components.address', [
                'wireModel' => "$saveTo.address1",
                'name' => 'address1',
                 'countriesResponse' => $countriesResponse
     ])

        @php($inputs++)
    @endunless

    @unless($requirements->address2->isHidden())

            @include('checkout::components.address', [
                 'wireModel' => "$saveTo.address2",
                 'name' => 'address2',
                 'countriesResponse' => $countriesResponse
                ])


        @php($inputs++)
    @endunless
