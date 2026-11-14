(() => {
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
