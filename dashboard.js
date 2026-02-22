// =============================================
//    TRUCK KOI — DASHBOARD FUNCTIONALITY
// =============================================

document.addEventListener('DOMContentLoaded', function () {



    // View Toggle Buttons
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Truck Card Click Handler
    const truckCards = document.querySelectorAll('.truck-card');
    truckCards.forEach(card => {
        card.addEventListener('click', function (e) {
            // Don't navigate if clicking the button directly
            if (!e.target.classList.contains('btn-view-details')) {
                // In production, this would navigate to the specific truck's tracking page
                console.log('Truck card clicked');
            }
        });
    });

    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe truck cards for animation
    truckCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Observe stat cards for animation
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });
});
