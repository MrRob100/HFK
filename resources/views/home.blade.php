@extends('layouts.app')

@section('content')
<pair-page>
</pair-page>
<div class="container">
    <table class="table-borderless">
        <thead>
        <th>Pair</th>
        <th>Count Above</th>
        <th>Count Middle</th>
        <th>SD Above</th>
        <th></th>
        </thead>
        <tbody>
        @foreach ($results as $result)
            <tr>
                <td>
                    {{ $result->pair }}
                </td>
                <td>
                    {{ $result->count_above }}
                </td>
                <td>
                    {{ $result->count_middle }}
                </td>
                <td>
                    {{ $result->sd_above }}
                </td>
                <td></td>
                <td>
                    <button class="btn btn-success btn-sm" id="show_pair_{{ $result->pair }}">show pair</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
