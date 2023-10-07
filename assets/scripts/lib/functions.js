function sprintf() {
    const args = arguments;
    let string = args[0];
    string = string.replace(/>\s+</g,'><');
    return string.replace(/{(\d+)}/g, function(match, number) { 
        return 'undefined' !== typeof args[number] ? args[number] : match;
    });
}

export {sprintf};
