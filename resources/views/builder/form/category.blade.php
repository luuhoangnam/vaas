<div class="form-group">
    <label class="">Category</label>
    <select class="form-control" name="account" value="{{ old('account') }}">
        @foreach($suggestedCategories as $category)
            @php
                $categoryID = (int)$category['Category']['CategoryID'];
                $categoryBreadcrumb = join(' &raquo; ', $category['Category']['CategoryParentName']);
                $percentFound = percent($category['PercentItemFound'] / 100, 0);
                $optionValue = "{$categoryBreadcrumb}&nbsp;{$category['Category']['CategoryName']} ({$percentFound})";
            @endphp

            <option value="{{ $categoryID }}">
                {!! $optionValue !!}
            </option>
        @endforeach
    </select>
</div>