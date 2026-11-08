(() => {
    const input = document.querySelector('[data-category-filter]');
    if (input) {
        const cards = [...document.querySelectorAll('[data-category-card]')];
        const clear = document.querySelector('[data-category-clear]');
        const counter = document.querySelector('[data-category-count]');
        const empty = document.querySelector('[data-category-empty]');
        function filter() {
            const term = input.value.trim().toLocaleLowerCase();
            let count = 0;
            cards.forEach(card => { card.hidden = !card.dataset.categoryName.includes(term); if (!card.hidden) count++; });
            clear.hidden = !input.value;
            empty.hidden = count !== 0;
            counter.textContent = `${count} ${count === 1 ? 'category' : 'categories'}${term ? ' found' : ''}`;
        }
        function reset() { input.value = ''; filter(); input.focus(); }
        input.addEventListener('input', filter);
        clear.addEventListener('click', reset);
        document.querySelector('[data-category-reset]')?.addEventListener('click', reset);
    }
    document.querySelectorAll('[data-help-center]').forEach(root => {
        const search = root.querySelector('[data-help-search]');
        const buttons = [...root.querySelectorAll('[data-help-topic]')];
        const articles = [...root.querySelectorAll('[data-help-faq]')];
        let topic = 'all';
        function filter() {
            const term = search.value.trim().toLocaleLowerCase();
            let count = 0;
            articles.forEach(article => {
                article.hidden = !(topic === 'all' || article.dataset.helpTopicName === topic) || !article.dataset.helpText.includes(term);
                if (!article.hidden) count++;
            });
            root.querySelector('[data-help-empty]').hidden = count !== 0;
        }
        search.addEventListener('input', filter);
        buttons.forEach(button => button.addEventListener('click', () => {
            topic = button.dataset.helpTopic;
            buttons.forEach(item => item.setAttribute('aria-pressed', String(item === button)));
            filter();
        }));
    });
})();
