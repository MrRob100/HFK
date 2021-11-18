@extends('layouts.app')

@section('content')
<pair-page>
</pair-page>
{{--<div class="container">--}}
{{--    <table class="table-borderless">--}}
{{--        <thead>--}}
{{--        <th>Pair</th>--}}
{{--        <th>Count Above</th>--}}
{{--        <th>Count Middle</th>--}}
{{--        <th>SD Above</th>--}}
{{--        <th></th>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        @foreach ($results as $key => $result)--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    {{ $result->symbol1 }}--}}
{{--                    {{ $result->symbol2 }}--}}
{{--                </td>--}}
{{--                <td>--}}
{{--                    {{ $result->count_above }}--}}
{{--                </td>--}}
{{--                <td>--}}
{{--                    {{ $result->count_middle }}--}}
{{--                </td>--}}
{{--                <td>--}}
{{--                    {{ $result->sd_above }}--}}
{{--                </td>--}}
{{--                <td></td>--}}
{{--                <td>--}}
{{--                    <button onclick="showResult({{ $key }})" class="btn btn-success btn-sm">show pair</button>--}}
{{--                    <input id="symbol1_{{ $key }}" type="hidden" value="{{ $result->symbol1 }}">--}}
{{--                    <input id="symbol2_{{ $key }}" type="hidden" value="{{ $result->symbol2 }}">--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--    </table>--}}
{{--</div>--}}
@endsection
