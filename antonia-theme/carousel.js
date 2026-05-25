// Book Carousel Functionality
document.addEventListener('DOMContentLoaded', function () {
    const bookItems = document.querySelectorAll('.book-item');
    const descriptionEl = document.querySelector('.book-description');
    const readNowLink = document.getElementById('bookReadNowLink');

    // Book data – populated from the WordPress Books CPT via wp_localize_script
    // (Appearance › Customize or WP Admin › Books to edit).
    // Falls back to placeholder entries so the carousel is never blank.
    const bookData = (window.antoniaCarouselBooks && window.antoniaCarouselBooks.length)
        ? window.antoniaCarouselBooks
        : [
            {
                title: "Ice and Fire",
                description: "This epic fantasy tale weaves together adventure, magic, and destiny in a world where nothing is as it seems. Follow the journey of heroes as they face impossible choices and discover the truth hidden beneath ancient prophecies.",
                link: "#"
            },
            {
                title: "The Heartless Prince",
                description: "A dark and captivating story of power, betrayal, and redemption. In a kingdom ruled by shadows, one prince must choose between his crown and his soul. A tale of darkness and light intertwined.",
                link: "#"
            },
            {
                title: "Shadows of the Realm",
                description: "An enchanting journey through forbidden lands and forgotten magic. When the veil between worlds grows thin, an unlikely hero must rise to face an ancient evil that threatens to consume everything.",
                link: "#"
            }
        ];

    // Read the active index from whichever .book-item has class 'active' (set server-side by PHP)
    let currentActiveIndex = Array.from(bookItems).findIndex(el => el.classList.contains('active'));
    if (currentActiveIndex < 0) currentActiveIndex = 0;

    // Initialize the description/link for the active book
    updateBookInfo(currentActiveIndex);

    function updateBookInfo(index, animate = false) {
        const book = bookData[index];

        if (animate) {
            // Fade out
            descriptionEl.classList.add('fade-out');

            // Wait for fade out, then update and fade in
            setTimeout(() => {
                descriptionEl.textContent = book.description;
                readNowLink.href = book.link;
                descriptionEl.classList.remove('fade-out');
            }, 200);
        } else {
            // Initial load without animation
            descriptionEl.textContent = book.description;
            readNowLink.href = book.link;
        }
    }

    function setActiveBook(clickedIndex) {
        if (clickedIndex === currentActiveIndex) {
            return; // Already active
        }

        // Remove active class from all
        bookItems.forEach(item => item.classList.remove('active'));

        // Add active class to clicked item
        bookItems[clickedIndex].classList.add('active');

        // Update the info section with animation
        updateBookInfo(clickedIndex, true);

        // Update current index
        currentActiveIndex = clickedIndex;
    }

    // Add click handlers to all book items
    bookItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            setActiveBook(index);
        });

        // Add keyboard accessibility
        item.setAttribute('tabindex', '0');
        item.setAttribute('role', 'button');
        item.setAttribute('aria-label', `Select ${bookData[index].title}`);

        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                setActiveBook(index);
            }
        });
    });
});

// Scroll-down container click handler
document.addEventListener('DOMContentLoaded', function () {
    const scrollDownContainer = document.querySelector('.scroll-down-container');
    const newContentSection = document.querySelector('.new-content-section');

    if (scrollDownContainer && newContentSection) {
        scrollDownContainer.style.cursor = 'pointer';

        scrollDownContainer.addEventListener('click', () => {
            const targetPosition = newContentSection.offsetTop;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        });
    }
});
