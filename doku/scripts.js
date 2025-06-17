// Initialize Mermaid JS for diagrams
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Mermaid diagrams
    mermaid.initialize({
        startOnLoad: true,
        theme: 'neutral',
        securityLevel: 'loose',
        flowchart: {
            useMaxWidth: true,
            htmlLabels: true
        }
    });

    // Handle navigation active states
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('nav a');

    // Add smooth scrolling to nav links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            window.scrollTo({
                top: targetSection.offsetTop - 70,
                behavior: 'smooth'
            });
            
            // Update URL hash without jumping
            history.pushState(null, null, `#${targetId}`);
            
            // Update active state
            updateActiveNavLink();
        });
    });

    // Update active nav link based on scroll position
    function updateActiveNavLink() {
        let currentSection = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.clientHeight;
            if (pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
                currentSection = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }

    // Listen for scroll events to update active nav link
    window.addEventListener('scroll', updateActiveNavLink);
    
    // Set active nav link on page load
    updateActiveNavLink();
});
