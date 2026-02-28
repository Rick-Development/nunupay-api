<div class="pagination-section">
<nav id="pagination" class="d-flex justify-content-end">
    @if ($paginator->hasPages())
        <ul class="pagination" data-wow-duration="1s" data-wow-delay="0.35s">
            @if ($paginator->onFirstPage())
                <li class="disabled page-item">
                    <a href="#" class="page-link" aria-label="Previous">
                        <span aria-hidden="true"><i class="fal fa-long-arrow-left"></i></span>
                        <span class="sr-only">@lang('Previous')</span>
                    </a>
                </li>
            @else
                <li class="page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="page-link" rel="prev"><i class="fal fa-long-arrow-left"></i></a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class=" page-item">
                        <a href="#" class="page-link">{{ $element }}</a>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <a href="#" class="page-link">{{ $page }}<span class="sr-only">(current)</span></a>
                            </li>
                        @else
                            <li class="page-item">
                                <a href="{{ $url}}" class="page-link">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="page-link" rel="next"><i class="fal fa-long-arrow-right"></i></a>
                </li>
            @else
                <li class="disabled page-item">
                    <a href="#" class="disabled page-link" aria-label="Next">
                        <span aria-hidden="true"><i class="fal fa-long-arrow-right"></i></span>
                        <span class="sr-only">@lang('Next')</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
</nav>
</div>
