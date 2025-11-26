@use(Nezasa\Checkout\Enums\Section)
@use(Nezasa\Checkout\Integrations\Nezasa\Enums\AnswerInputEnum)
@use(Nezasa\Checkout\Supporters\AutoCompleteSupporter)

@php($state = $isExpanded ? 'editing' : 'valid')


<x-checkout::editable-box
    title="{{trans('checkout::page.trip_details.activity_details')}}"
    :state="$state"
    :showEdit="true"
    :showCheck="$isCompleted"
    class="{{$shouldRender ? '' : 'hidden'}}"
    onEdit="expand('{{Section::Activity->value}}')"
>
    <form wire:submit="save">
        @foreach($activityQuestions as $componentIndex =>  $component)
            <h2 class="text-xl font-semibold mb-4">{{$component->productName}}</h2>
            <div class="py-2 bg-white dark:bg-gray-800 @if($componentIndex !== $currentActivity) hidden @endif"
                 id="activity-{{$component->componentId}}">
                <div id="{{$component->componentId}}">
                    @foreach($component->questions as $questionIndex => $question)
                        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 min-w-0 mb-5">
                            <div class="space-y-2 w-full min-w-0">

                                @if(! $question->getInputType()->isCheckbox())
                                    <label
                                        class="block text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
                                        {{$question->question}} @if($question->required)
                                            ({{trans('checkout::page.trip_details.required')}})
                                        @endif
                                    </label>
                                @endif

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
                                    @case(AnswerInputEnum::Checkbox)
                                        <label
                                            class="flex items-center space-x-2 text-gray-700 dark:text-gray-200 font-medium overflow-ellipsis whitespace-nowrap overflow-hidden">
                                            <input
                                                name="result.{{$component->componentId}}.{{$question->refId}}"
                                                wire:model.blur="result.{{$component->componentId}}.{{$question->refId}}"
                                                type="checkbox" value="1" class="form-checkbox ml-1">
                                            <span> {{$question->question}} @if($question->required)
                                                    ({{trans('checkout::page.trip_details.required')}})
                                                @endif</span>
                                        </label>
                                        @break
                                    @default
                                        <input
                                            name="result.{{$component->componentId}}.{{$question->refId}}"
                                            type="text"
                                            @if($question->placeholder) placeholder="{{$question->placeholder}}" @endif
                                            wire:model.blur="result.{{$component->componentId}}.{{$question->refId}}"
                                            class="form-input w-full">
                                @endswitch
                                @error("result.$component->componentId.$question->refId")<span
                                    class="text-red-500 text-sm">{{ $message }}</span>@enderror

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div
                class="flex justify-between items-center mt-2 mb-4 @if($componentIndex !== $currentActivity) hidden @endif">
                @if(!$loop->first)
                    @php($previousLabel = trans('checkout::page.trip_details.previous_activity'))
                @endif
                <button type="button"
                        wire:click="previousActivity('{{$componentIndex}}')"
                        class="inline-flex items-center px-5 py-2 rounded-lg border border-gray-200 bg-white text-blue-600 hover:bg-gray-50 shadow-sm {{ isset($previousLabel) ? '' : 'invisible' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="font-medium">{{$previousLabel ?? ''}}</span>
                </button>

                @if($loop->remaining)
                    @php($label = trans('checkout::page.trip_details.next_activity'))
                @endif

                @if($loop->last )
                    @php($label = trans('checkout::page.trip_details.next_step'))
                @endif
                <button type="button"
                        wire:click="nextActivity('{{$componentIndex}}')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md">
                    {{$label}}
                </button>
            </div>

            @if(!$loop->last)
                <div class="h-px bg-gray-200 dark:bg-gray-700 -mx-8 mt-6 mb-6"></div>
            @endif
        @endforeach
    </form>
</x-checkout::editable-box>

