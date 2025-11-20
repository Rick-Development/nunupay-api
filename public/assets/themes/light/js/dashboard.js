"use strict";
// Preloader area
const preloader = document.getElementById("preloader");

function preloader_function() {
    preloader.style.display = "none";
}

const toggleSideMenu = () => {
    document.body.classList.toggle("toggle-sidebar");
};
const hideSidebar = () => {
    document.getElementById("formWrapper").classList.remove("active");
    document.getElementById("formWrapper2").classList.remove("active");
};

// input file preview & reset start
const previewImage = (id) => {
    document.getElementById(id).src = URL.createObjectURL(event.target.files[0]);
};

// Tooltip
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));


$(document).ready(function () {

    $('.card-carousel').owlCarousel({
        loop: false,
        nav: false,
        dots: false,
        responsive: {
            0: {
                items: 1,
                margin: -40,
            },
            600: {
                items: 2,
                margin: -40,
            },
            992: {
                items: 1,
                margin: -100,
            },
            1400: {
                items: 2,
                margin: -40,
            },
            1600: {
                items: 2,
                margin: -100,
            },
        }
    });

    // Nice select start
    if ($('.nice-select').length) {
        $('.nice-select').niceSelect();
    }

    // cmn select2 start
    $(document).ready(function () {
        $('.cmn-select2').select2();
    });
    $(".modal-select").select2({
        dropdownParent: $("#formModal"),
    });

    // RichTextEditor start
    if ($('#div_editor1').length) {
        var editor1cfg = {}
        editor1cfg.toolbar = "basic";
        var editor1 = new RichTextEditor("#div_editor1", editor1cfg);
    }

    /*flatpickr start*/
    flatpickr("#datePick", {});

});


// Dark theme start

const toggleBtn = document.getElementById("toggle-btn");
const body = document.querySelector("body");
const moon = document.getElementById("moon");
const sun = document.getElementById("sun");
const headElement = document.querySelector('head');
const notFoundLight = headElement.getAttribute('data-notfoundlight');
const notFoundDark = headElement.getAttribute('data-notfounddark');
const notFoundImage = document.getElementById("notFoundImage");
const notFoundImage2 = document.getElementById("notFoundImage2");

toggleBtn.addEventListener("click", function () {
    document.body.classList.toggle("dark-theme");
    if (document.body.classList.contains("dark-theme")) {
        localStorage.setItem("dark-theme", 1);
    } else {
        localStorage.setItem("dark-theme", 0);
    }
    setTheme();
});

function setTheme() {
    const isDarkTheme = localStorage.getItem("dark-theme");
    if (isDarkTheme == 1) {
        body.classList.add('dark-theme');
        moon.style.display = "none";
        sun.style.display = "block";
        if (notFoundImage != null) {
            notFoundImage.src = notFoundLight;
        }
        if (notFoundImage2 !=null){
            notFoundImage2.src = notFoundLight;
        }
    } else {
        body.classList.remove('dark-theme');
        moon.style.display = "block";
        sun.style.display = "none";
        if (notFoundImage != null) {
            notFoundImage.src = notFoundDark;
        }
        if (notFoundImage2 !=null){
            notFoundImage2.src = notFoundDark;
        }
    }
}

setTheme();

// Dark theme end
