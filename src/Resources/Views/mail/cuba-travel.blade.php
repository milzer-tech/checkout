@php use Illuminate\Support\Carbon;use Nezasa\Checkout\Integrations\Nezasa\Dtos\Payloads\Entities\PaxInfoPayloadEntity; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Traveller Information' }}</title>
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:Arial, sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
    <tr>
        <td align="center">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                   style="background:#ffffff; border-radius:12px; padding:40px; border:1px solid #e5e7eb;">

                <tr>
                    <td style="font-size:24px; font-weight:700; color:#111827; padding-bottom:20px;">
                        Traveller Information: {{$checkout->itinerary_id}}
                    </td>
                </tr>

                <tr>
                    <td style="font-size:15px; color:#4b5563; line-height:1.7; padding-bottom:25px;">
                        Here are the traveller details:
                    </td>
                </tr>

                <!-- LOOP TRAVELLERS -->

                @foreach ($travelers as $index => $traveller)

                    <!-- Traveller Header -->
                    <tr>
                        <td style="padding:10px 0 5px; font-size:18px; font-weight:600; color:#1f2937;">
                            Traveller {{ $index + 1 }} @php
                                if(isset($traveller['isMainContact']) ){
                                    if($traveller['isMainContact'] === true){
                                        echo '(the main contact)';
                                    }
                                    unset($traveller['isMainContact']);
                                }
                            @endphp
                        </td>
                    </tr>

                    <!-- ONE SINGLE BOX FOR THE WHOLE TRAVELLER -->
                    <tr>
                        <td>
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#f9fafb; border-radius:10px; padding:10px 20px; border:1px solid #e5e7eb; margin-bottom:20px;">
                                @foreach(collect($traveller)->chunk(2) as $chunk)
                                    <tr>

                                        @foreach($chunk as $name => $value)
                                            <td width="50%" style="padding:6px 10px;">
                                                <strong
                                                    style="color:#111827; font-size:14px;">{{ trans("checkout::input.attributes.$name") }}
                                                    :</strong><br>
                                                <span style="color:#374151; font-size:14px;">
                                                    @if($value instanceof Carbon)
                                                        {{ $value->toDateString() }}
                                                    @elseif($value instanceof BackedEnum)
                                                        {{ $value->value }}
                                                    @elseif($name === 'address')
                                                        {{implode(',', $value)}}
                                                    @else
                                                        {{$value}}
                                                    @endif
                                                 </span>
                                            </td>
                                        @endforeach

                                        {{-- If the row has only 1 column, fill the other side to keep layout consistent --}}
                                        @if($chunk->count() == 1)
                                            <td width="50%"></td>
                                        @endif

                                    </tr>
                                @endforeach

                            </table>
                        </td>
                    </tr>

                @endforeach

                <!-- END LOOP -->

                <!-- BUTTON -->

                <tr>
                    <td align="center" style="padding:30px 0;">
                        <a href="{{config('checkout.nezasa.base_url')}}/itineraries/{{$checkout->itinerary_id}}/travel-summary"
                           style="background:#2563eb; color:#ffffff; padding:14px 30px;
                                  font-size:16px; border-radius:8px; text-decoration:none;">
                            {{trans('checkout::page.trip_details.view_full_itinerary')}}
                        </a>
                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td style="padding-top:25px; font-size:12px; color:#9ca3af; text-align:center;">
                        {{ config('app.name') }} — All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
