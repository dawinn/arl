'use strict';
(function () {

  /*** slider ***/
  var controls = document.querySelector('.controls');

  var slides = document.querySelectorAll('.slides .slide');
  var currentSlide = 0;
  var slideInterval = setInterval(nextSlide,7000);

  function nextSlide(){
    goToSlide(currentSlide+1);
  }

  function previousSlide(){
    goToSlide(currentSlide-1);
  }

  function goToSlide(n){
    slides[currentSlide].className = 'slide';
    currentSlide = (n+slides.length)%slides.length;
    slides[currentSlide].className = 'slide  slide--show';
  }

  var playing = true;
  var pauseButton = controls.querySelector('.pause');

  function pauseSlideshow(){
    pauseButton.innerHTML = '&#9658;'; // play character
    pauseButton.classList.toggle('play', true);
    pauseButton.classList.toggle('pause', false);
    playing = false;
    clearInterval(slideInterval);
  }

  function playSlideshow(){
    pauseButton.innerHTML = '&#10074;&#10074;'; // pause character
    pauseButton.classList.toggle('play', false);
    pauseButton.classList.toggle('pause', true);
    playing = true;
    slideInterval = setInterval(nextSlide,7000);
  }

  pauseButton.addEventListener('click', function(){
    if(playing){ pauseSlideshow(); }
    else{ playSlideshow(); }
  });

  var next = controls.querySelector('.next');
  var previous = controls.querySelector('.previous');

  next.addEventListener('click', function(){
    pauseSlideshow();
    nextSlide();
  });

  previous.addEventListener('click', function(){
    pauseSlideshow();
    previousSlide();
  });
/*** /slider ***/


})();
