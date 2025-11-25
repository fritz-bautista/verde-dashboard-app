<h2>Semester Ranking: {{ $ranking->semester_name }}</h2>
<p>Start: {{ $ranking->started_at }}, Stop: {{ $ranking->stopped_at ?? 'N/A' }}</p>
<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>College</th>
            <th>Points</th>
        </tr>
    </thead>
    <tbody>
        @foreach($colleges as $r)
            <tr>
                <td>{{ $r->college->name }}</td>
                <td>{{ $r->score }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
