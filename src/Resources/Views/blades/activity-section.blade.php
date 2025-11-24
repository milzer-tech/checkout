@use(Nezasa\Checkout\Enums\Section)
@use(Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerInputEnum)
@use(Nezasa\Checkout\Supporters\AutoCompleteSupporter)

@php($state = $isExpanded ? 'editing' : 'valid')


<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.activity_details')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    onEdit="expand('{{Section::Activity->value}}')"
>

    @foreach($activityQuestions as $componentIndex =>  $component)
        <div class="py-2 bg-white dark:bg-gray-800">
            <h2 class="text-xl font-semibold mb-8">{{$component->productName}}</h2>

            <div id="{{$component->componentId}}">
                @foreach($component->questions as $questionIndex => $question)
                    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 min-w-0 mb-5">
                        <div class="space-y-2 w-full min-w-0">
                            <label
                                class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
                                {{$question->question}} @if($question->required)
                                    ({{trans('checkout::page.trip_details.required')}})
                                @endif
                            </label>

                            @switch($question->getInputType())
                                @case(AnswerInputEnum::Select)
                                    <select name="result.{{$component->componentId}}.{{$question->refId}}"
                                            wire:model.change="result.{{$component->componentId}}.{{$question->refId}}"
                                            class="form-select pr-8 w-full">
                                        <option>Select</option>
                                        @foreach($question->answerOptions as $option)
                                            <option value="{{$option->refId}}">{{$option->displayName}}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case(AnswerInputEnum::Radio)
                                    @break
                                @default
                                    <input
                                        name="contact.firstName"
                                        type="text"
                                        @if($question->placeholder) placeholder="{{$question->placeholder}}" @endif
                                        wire:model.blur="contact.firstName"
                                        class="form-input w-full">
                            @endswitch
                            @error("result.$component->componentId.$question->refId")<span
                                class="text-red-500 text-sm">{{ $message }}</span>@enderror

                        </div>
                    </div>
                @endforeach
            </div>
            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mb-8"></div>
            @endforeach

            <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8"></div>

            <div class="flex justify-between items-center mt-8">
                <button type="button"
                        wire:click="next"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    {{trans('checkout::page.trip_details.back')}}
                </button>

                <button type="button"
                        wire:click="next"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    {{trans('checkout::page.trip_details.next')}}
                </button>
            </div>
        </div>
</x-checkout::editable-box>

