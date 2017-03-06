<table class="table">
    <tr>
        <th></th>
        <th>Description</th>
    </tr>
    @foreach($items as $item)
        <tr>
            <td width="135px">
                <img src="{!! $item->picture !!}" style="max-width:135px;max-height:135px">
            </td>
            <td width="50%">
                <a href="{!! $item->link !!}">{!! $item->name !!}</a>
            </td>
            <td class="text-right">
                {!! $item->price !!}
            </td>
        </tr>
    @endforeach
</table>