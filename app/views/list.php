@extends layouts/master

<div class="container">

    <h1>Spillere</h1>

    <table class="table tablesorter">
        <thead>
            <tr>
                <th>Navn</th>
                <th>Steam ID</th>
                <th>Kills</th>
                <th>Deaths</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($players as $player):
            <tr>
                <td>{{ htmlspecialchars($player->name) }}</td>
                <td>{{ $player->steamid }}</td>
                <td>{{ $player->kills }}</td>
                <td>{{ $player->deaths }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
