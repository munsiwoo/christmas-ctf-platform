window.onload = () => {
    $.get('/get_countdown', (res) => {
        const SECONDS = 1000;
        const MINUTE = SECONDS * 60;
        const HOUR = MINUTE * 60;
        const DAY = HOUR * 24;
        
        let countdown = new Date(res);
        let timer = setInterval(refresh, 1000);
        setTimeout(refresh, 100);

        function pad(n, width) {
            n = n + '';
            return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
        }

        function refresh() {
            let distance = countdown - new Date();
            let left = {};

            left.day = pad(Math.floor(distance / DAY), 2);
            left.hour = pad(Math.floor((distance % DAY) / HOUR), 2);
            left.minute = pad(Math.floor((distance % HOUR) / MINUTE), 2);
            left.seconds = pad(Math.floor((distance % MINUTE) / SECONDS), 2);

            if(left.day <= 0) left.day = 0;
            if(left.hour <= 0) left.hour = 0;
            if(left.minute <= 0) left.minute = 0;
            if(left.seconds <= 0) left.seconds = 0;

            $('#day').text(left.day);
            $('#hour').text(left.hour);
            $('#minute').text(left.minute);
            $('#seconds').text(left.seconds);

            if(left.day+left.hour+left.minute+left.seconds == 0) {
                clearInterval(timer);
            }
        }
    });
};