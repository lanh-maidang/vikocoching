document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.container-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            contents.forEach(c => {
                if (c.getAttribute('data-content') === target) {
                    c.classList.add('active');
                } else {
                    c.classList.remove('active');
                }
            });
        });
    });

    if (tabs[0]) {
        tabs[0].click();
    }
});
