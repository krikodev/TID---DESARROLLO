document.addEventListener('DOMContentLoaded', function () {
    function isMobile() {
        return window.innerWidth <= 768;
    }

    const searchWrapper = document.getElementById('floating-search');
    const searchContainer = searchWrapper.querySelector('.search-container');

    const placeholder = document.createElement('div');
    placeholder.className = 'search-placeholder';
    searchWrapper.parentNode.insertBefore(placeholder, searchWrapper.nextSibling);

    let searchOffsetTop = searchWrapper.offsetTop;
    let isSticky = false;
    let ticking = false;

    function handleScroll() {
        if (isMobile()) {
            if (isSticky) {
                searchWrapper.classList.remove('sticky');
                placeholder.classList.remove('active');
                placeholder.style.height = '0px';
                isSticky = false;
            }
            return;
        }

        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop >= searchOffsetTop && !isSticky) {
            isSticky = true;
            const searchHeight = searchWrapper.offsetHeight;
            searchWrapper.classList.add('sticky');
            placeholder.classList.add('active');
            placeholder.style.height = searchHeight + 'px';

        } else if (scrollTop < searchOffsetTop && isSticky) {
            isSticky = false;
            searchWrapper.classList.remove('sticky');
            placeholder.classList.remove('active');
            placeholder.style.height = '0px';
        }
    }

    function optimizedHandleScroll() {
        if (!ticking) {
            requestAnimationFrame(function () {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    }

    function recalculatePosition() {
        if (isMobile()) return;
        if (!isSticky) {
            searchOffsetTop = searchWrapper.offsetTop;
        }
    }

    function handleResize() {
        if (isMobile() && isSticky) {
            searchWrapper.classList.remove('sticky');
            placeholder.classList.remove('active');
            placeholder.style.height = '0px';
            isSticky = false;
        } else if (!isMobile()) {
            recalculatePosition();
        }
    }

    // Listeners
    window.addEventListener('scroll', optimizedHandleScroll);
    window.addEventListener('resize', handleResize);
    window.addEventListener('load', recalculatePosition);

    // Exponer función global por si la necesitas externamente
    window.recalculateSearchPosition = recalculatePosition;
});
