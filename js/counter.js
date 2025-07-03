document.addEventListener('DOMContentLoaded', () => {
    function startCounter() {
        const counters = document.querySelectorAll('.counter-number');
        counters.forEach(counter => {
            counter.innerText = '0+';
            const target = +counter.getAttribute('data-target');
            
            const updateCounter = () => {
                const current = parseInt(counter.innerText.replace('+', ''));
                const increment = target / 100;

                if (current < target) {
                    counter.innerText = `${Math.ceil(current + increment)}+`;
                    setTimeout(updateCounter, 50);
                } else {
                    counter.innerText = `${target}+`;
                }
            };
            updateCounter();
        });
    }

    // Observe when .counter-section is in view
    let observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounter();
                observer.disconnect(); // Only once
            }
        });
    });

    const counterSection = document.querySelector('.counter-section');
    if (counterSection) {
        observer.observe(counterSection);
    }
});
