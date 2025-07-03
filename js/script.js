// js/script.js

const nav = document.querySelector('.test_nav')
fetch('navbar.html')
.then(res=>res.text())
.then(data=>{
    nav.innerHTML=data
})

const footer = document.querySelector('.end')
fetch('footer.html')
.then(res=>res.text())
.then(data=>{
    footer.innerHTML=data
})


document.addEventListener('contextmenu', function (event) {
    event.preventDefault(); // Disable right-click menu
});

document.addEventListener('copy', function (event) {
    event.preventDefault(); // Disable copying
});
