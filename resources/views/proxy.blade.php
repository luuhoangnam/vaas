function FindProxyForURL(url, host) {
    const proxies = [
        @foreach($proxies as $proxy)
            '{{ $proxy }}',
        @endforeach
    ];

    const rand = Math.floor(Math.random() * proxies.length)

    return "PROXY " + proxies[rand]
}