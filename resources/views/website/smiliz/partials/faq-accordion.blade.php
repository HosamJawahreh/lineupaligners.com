@php
    $accordionId = $accordionId ?? 'faqAccordion';
    $items = $items ?? [];
@endphp
<div class="accordion lineup-faq-accordion" id="{{ $accordionId }}">
    @foreach($items as $item)
    @php
        $headingId = $accordionId . 'Heading' . $loop->index;
        $collapseId = $accordionId . 'Collapse' . $loop->index;
        $isFirst = $loop->first;
    @endphp
    <div class="accordion-item{{ $isFirst ? ' active' : '' }}">
        <h2 class="accordion-header" id="{{ $headingId }}">
            <button class="accordion-button{{ $isFirst ? '' : ' collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                <span class="pbmit-accordion-title">
                    <span class="nub">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}.</span>
                    {{ $item['question'] }}
                </span>
                <span class="pbmit-accordion-icon">
                    <span class="pbmit-accordion-icon-closed">
                        <svg aria-hidden="true" class="e-font-icon-svg e-fas-plus" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path></svg>
                    </span>
                    <span class="pbmit-accordion-icon-opened">
                        <svg aria-hidden="true" class="e-font-icon-svg e-fas-minus" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path></svg>
                    </span>
                </span>
            </button>
        </h2>
        <div id="{{ $collapseId }}" class="accordion-collapse collapse{{ $isFirst ? ' show' : '' }}" aria-labelledby="{{ $headingId }}" data-bs-parent="#{{ $accordionId }}">
            <div class="accordion-body">
                <p>{{ $item['answer'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
