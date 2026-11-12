@auth
    <form id="dockQuickStartForm" action="{{ route('ujian.buat') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="nama" value="{{ Auth::user()->name }}">
        <input type="hidden" id="dockQuickStartPaket" name="paket" value="D">
        <input type="hidden" id="dockQuickStartExamModeId" name="exam_mode_id" value="">
        <input type="hidden" id="dockQuickStartSubCategoryId" name="sub_category_id" value="">
        <input type="hidden" id="dockQuickStartSubTopik" name="sub_topik" value="">
    </form>

    @unless($examMode)
        @include('ujian.partials.start_exam_modal_plain')
    @endunless
@endauth
