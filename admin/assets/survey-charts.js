/*
 * Draws the survey charts on the admin dashboard.
 *
 * Fetches the aggregated figures from stats.php and renders one Chart.js
 * chart per question: a doughnut for single-choice, a bar for multi-choice,
 * and a bar for the 1-10 scale. Free-text questions just show a count.
 */
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('surveyCharts');
    if (!container || typeof Chart === 'undefined') return;

    const from = container.getAttribute('data-from') || '';
    const to = container.getAttribute('data-to') || '';

    const params = new URLSearchParams();
    if (from) params.set('from', from);
    if (to) params.set('to', to);

    const palette = [
        '#1f6b4f', '#d9a441', '#174535', '#4f8f74',
        '#b8860b', '#0f2f24', '#7fb69c', '#c9a24a',
        '#2e7d5b', '#8aa79a'
    ];

    function title(question) {
        return question.number + '. ' + question.title;
    }

    function makeCard(question) {
        const card = document.createElement('article');
        card.className = 'survey-chart-card';

        const heading = document.createElement('h3');
        heading.className = 'survey-chart-title';
        heading.textContent = title(question);
        card.appendChild(heading);

        return card;
    }

    function makeCanvas(card) {
        const wrap = document.createElement('div');
        wrap.className = 'survey-chart-canvas';
        const canvas = document.createElement('canvas');
        wrap.appendChild(canvas);
        card.appendChild(wrap);
        return canvas;
    }

    function addNote(card, text) {
        const note = document.createElement('p');
        note.className = 'survey-chart-note';
        note.textContent = text;
        card.appendChild(note);
    }

    function drawChoiceChart(question) {
        const card = makeCard(question);
        const canvas = makeCanvas(card);
        container.appendChild(card);

        const labels = question.options.map(function (option) { return option.label; });
        const values = question.options.map(function (option) { return option.count; });

        if (question.type === 'radio') {
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: labels.map(function (label, index) {
                            return palette[index % palette.length];
                        }),
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    }
                }
            });
        } else {
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Selections',
                        data: values,
                        backgroundColor: '#1f6b4f'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } },
                        y: { ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    }

    function drawScaleChart(question) {
        const card = makeCard(question);
        const canvas = makeCanvas(card);

        if (question.average !== null && question.average !== undefined) {
            addNote(card, 'Average: ' + question.average + ' (from ' + question.answered + ' answers)');
        } else {
            addNote(card, 'No answers yet.');
        }

        container.appendChild(card);

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: question.values.map(function (item) { return String(item.value); }),
                datasets: [{
                    label: 'Responses',
                    data: question.values.map(function (item) { return item.count; }),
                    backgroundColor: '#d9a441'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { title: { display: true, text: question.min_label + ' → ' + question.max_label, font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    function drawTextCard(question) {
        const card = makeCard(question);
        addNote(card, (question.answered || 0) + ' written answer(s). See the list below.');
        container.appendChild(card);
    }

    fetch('stats.php' + (params.toString() ? '?' + params.toString() : ''), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            container.innerHTML = '';

            if (!data.success) {
                container.innerHTML = '<p class="admin-muted">Could not load charts.</p>';
                return;
            }

            if (!data.questions || !data.questions.length) {
                container.innerHTML = '<p class="admin-muted">No questions to chart.</p>';
                return;
            }

            data.questions.forEach(function (question) {
                if (question.type === 'radio' || question.type === 'checkbox') {
                    drawChoiceChart(question);
                } else if (question.type === 'scale') {
                    drawScaleChart(question);
                } else {
                    drawTextCard(question);
                }
            });
        })
        .catch(function () {
            container.innerHTML = '<p class="admin-muted">Could not load charts.</p>';
        });
});
