function getTimeRemaining(endtime) {
    var t = Date.parse(endtime) - Date.parse(new Date());
    var seconds = Math.floor((t / 1000) % 60);
    var minutes = Math.floor((t / 1000 / 60) % 60);
    var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
    var days = Math.floor(t / (1000 * 60 * 60 * 24));
    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}

function initializeClock(endtime) {
    var daysSpan = document.getElementById('timer-d'),
        hoursSpan = document.getElementById('timer-h'),
        minutesSpan = document.getElementById('timer-m');

    function updateClock() {
        var t = getTimeRemaining(endtime);

        daysSpan.innerHTML = t.days;
        hoursSpan.innerHTML = t.hours;
        minutesSpan.innerHTML = t.minutes;

        if (t.total <= 0) {
            clearInterval(timeInterval);
        }
    }

    updateClock();
    var timeInterval = setInterval(updateClock, 1000 * 60);
}


function initPopup() {
    var opened = false,
        $button = $('[data-popup]'),
        $popup = $('.popup');

    $button.on('click', function (e) {
        e.preventDefault();
        popupOpen($(this).data('popup'));
    });

    $('.popup__close').on('click', function (e) {
        e.preventDefault();
        popupClose();
    });

    function popupOpen(id) {
        if (!opened) {
            $popup.fadeIn();
        }

        $('body').css('overflow', 'hidden');

        $('.popup__content').hide();
        $(id).show();

        opened = true;
    }

    function popupClose() {
        $popup.fadeOut();
        $('body').css('overflow', '');
        opened = false;
    }


}


$(document).ready(function () {

    var deadline = new Date(Date.parse(new Date()) + 14 * 24 * 60 * 60 * 1000 + 13 * 60 * 60 * 1000 + 30 * 60 * 1000);
    initializeClock(deadline);

    initPopup();


    $('form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this),
            data = $(this).serialize(),
            id = $(this).attr('id'),
            $submitBtn = $(this).find('button[type="submit"]'),
            submitBtnText = $submitBtn.text();


        $.ajax({
            type: "POST",
            url: '/mail.php',
            data: data,
            beforeSend: function () {
                $submitBtn.attr('disabled', '');
                $submitBtn.text('Отправка...');
            },
            error: function (error) {
                alert('Ошибка ' + error.status + '. Повторите позднее.');
                $submitBtn.removeAttr('disabled');
                $submitBtn.text(submitBtnText);
            },
            success: function (data) {
                $submitBtn.removeAttr('disabled');
                $submitBtn.text(submitBtnText);

                data = JSON.parse(data);

                if (data.sended) {
                    switch (id) {
                        case 'step-1':

                            $('.popup__content').hide();
                            $('#step-2').show();

                            break;
                    }

                } else {
                    alert(data.message);
                }

            }
        });
    });
});