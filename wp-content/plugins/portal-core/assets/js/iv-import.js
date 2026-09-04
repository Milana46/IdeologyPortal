(function () {
    if (typeof window.portalIvImport !== 'object' || !window.portalIvImport) {
        return;
    }

    var cfg = window.portalIvImport;
    var overlay = null;
    var previewCards = [];

    function el(tag, attrs, text) {
        var node = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (key) {
                if (key === 'style') {
                    node.setAttribute('style', attrs[key]);
                } else if (key.indexOf('on') === 0) {
                    node[key] = attrs[key];
                } else {
                    node.setAttribute(key, attrs[key]);
                }
            });
        }
        if (text) {
            node.textContent = text;
        }
        return node;
    }

    function parseHtmlTables(html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var tables = doc.querySelectorAll('table');
        var best = [];
        tables.forEach(function (table) {
            var rows = [];
            table.querySelectorAll('tr').forEach(function (tr) {
                var cells = [];
                tr.querySelectorAll('th,td').forEach(function (cell) {
                    cells.push((cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim());
                });
                if (cells.some(function (c) { return c !== ''; })) {
                    rows.push(cells);
                }
            });
            if (rows.length > best.length) {
                best = rows;
            }
        });
        return best;
    }

    function parsePlain(text) {
        return String(text || '')
            .split(/\r?\n/)
            .map(function (line) {
                return line.split('\t').map(function (c) { return c.replace(/\s+/g, ' ').trim(); });
            })
            .filter(function (row) {
                return row.some(function (c) { return c !== ''; });
            });
    }

    function setStatus(box, text, isError) {
        box.textContent = text || '';
        box.style.color = isError ? '#b42318' : '#243b7a';
    }

    function renderPreview(box, cards) {
        box.innerHTML = '';
        if (!cards.length) {
            return;
        }
        var table = el('table', {
            style: 'width:100%;border-collapse:collapse;font-size:12px;color:#243b7a;'
        });
        var head = el('tr');
        ['ФИО', 'Место работы (учебы)', 'Должность', 'Телефон'].forEach(function (label) {
            head.appendChild(el('th', {
                style: 'text-align:left;padding:6px;border-bottom:1px solid #dbe4f2;background:#f3f7fd;'
            }, label));
        });
        table.appendChild(head);
        cards.slice(0, 40).forEach(function (card) {
            var tr = el('tr');
            ['fio', 'workplace', 'position', 'phone'].forEach(function (key) {
                tr.appendChild(el('td', {
                    style: 'padding:6px;border-bottom:1px solid #eef3fb;vertical-align:top;'
                }, card[key] || ''));
            });
            table.appendChild(tr);
        });
        box.appendChild(table);
        if (cards.length > 40) {
            box.appendChild(el('p', {
                style: 'margin:8px 0 0;font-size:12px;color:#5a6b8a;'
            }, 'Показаны первые 40 из ' + cards.length + '.'));
        }
    }

    function postForm(data) {
        return fetch(cfg.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (res) {
            return res.json();
        });
    }

    function parseRows(rows, statusBox, previewBox, importBtn) {
        var fd = new FormData();
        fd.append('action', 'portal_iv_parse_table');
        fd.append('nonce', cfg.nonce);
        fd.append('rows', JSON.stringify(rows));
        setStatus(statusBox, 'Разбираю таблицу…', false);
        return postForm(fd).then(function (json) {
            if (!json || !json.success) {
                previewCards = [];
                importBtn.disabled = true;
                setStatus(statusBox, (json && json.data && json.data.message) || 'Не удалось разобрать таблицу.', true);
                renderPreview(previewBox, []);
                return;
            }
            previewCards = json.data.cards || [];
            importBtn.disabled = previewCards.length === 0;
            setStatus(statusBox, 'Найдено карточек: ' + previewCards.length + '. Проверьте и нажмите «Создать карточки».', false);
            renderPreview(previewBox, previewCards);
        }).catch(function () {
            previewCards = [];
            importBtn.disabled = true;
            setStatus(statusBox, 'Ошибка сети при разборе таблицы.', true);
        });
    }

    function closeOverlay() {
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
        overlay = null;
        previewCards = [];
    }

    function openOverlay() {
        if (overlay) {
            return;
        }

        overlay = el('div', {
            style: 'position:fixed;inset:0;z-index:999999;background:rgba(20,32,64,.45);display:flex;align-items:center;justify-content:center;padding:20px;'
        });

        var panel = el('div', {
            style: 'width:min(860px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:16px;box-shadow:0 16px 40px rgba(31,64,128,.18);padding:22px 22px 18px;font-family:inherit;'
        });

        panel.appendChild(el('h2', {
            style: 'margin:0 0 8px;font-size:20px;color:#243b7a;'
        }, 'Импорт в «Идеологическую вертикаль»'));
        panel.appendChild(el('p', {
            style: 'margin:0 0 14px;font-size:13px;line-height:1.4;color:#5a6b8a;'
        }, 'Выберите .docx с таблицей или вставьте таблицу из Word (Ctrl+V). Колонки: ФИО, место работы (учебы), должность, телефон.'));

        var file = el('input', { type: 'file', accept: '.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
        var paste = el('textarea', {
            rows: '6',
            placeholder: 'Вставьте таблицу сюда',
            style: 'width:100%;margin-top:10px;box-sizing:border-box;padding:10px;border:1px solid #dbe4f2;border-radius:10px;font:13px/1.4 inherit;resize:vertical;'
        });
        var statusBox = el('p', { style: 'margin:12px 0 8px;font-size:13px;min-height:1.2em;color:#243b7a;' });
        var previewBox = el('div', {
            style: 'margin:0 0 14px;max-height:240px;overflow:auto;border:1px solid #e2eaf5;border-radius:10px;'
        });
        var importBtn = el('button', {
            type: 'button',
            style: 'margin:0;padding:10px 16px;border:0;border-radius:10px;background:#243b7a;color:#fff;font:600 13px inherit;cursor:pointer;'
        }, 'Создать карточки');
        importBtn.disabled = true;
        var closeBtn = el('button', {
            type: 'button',
            style: 'margin:0;padding:10px 16px;border:1px solid #dbe4f2;border-radius:10px;background:#fff;color:#243b7a;font:600 13px inherit;cursor:pointer;'
        }, 'Закрыть');

        var actions = el('div', { style: 'display:flex;gap:10px;flex-wrap:wrap;align-items:center;' });
        actions.appendChild(importBtn);
        actions.appendChild(closeBtn);

        file.addEventListener('change', function () {
            if (!file.files || !file.files[0]) {
                return;
            }
            var fd = new FormData();
            fd.append('action', 'portal_iv_parse_table');
            fd.append('nonce', cfg.nonce);
            fd.append('file', file.files[0]);
            setStatus(statusBox, 'Читаю Word-файл…', false);
            postForm(fd).then(function (json) {
                if (!json || !json.success) {
                    previewCards = [];
                    importBtn.disabled = true;
                    setStatus(statusBox, (json && json.data && json.data.message) || 'Не удалось прочитать файл.', true);
                    renderPreview(previewBox, []);
                    return;
                }
                previewCards = json.data.cards || [];
                importBtn.disabled = previewCards.length === 0;
                setStatus(statusBox, 'Найдено карточек: ' + previewCards.length + '. Проверьте и нажмите «Создать карточки».', false);
                renderPreview(previewBox, previewCards);
            }).catch(function () {
                previewCards = [];
                importBtn.disabled = true;
                setStatus(statusBox, 'Ошибка сети при чтении файла.', true);
            });
        });

        paste.addEventListener('paste', function (event) {
            var html = event.clipboardData ? event.clipboardData.getData('text/html') : '';
            var text = event.clipboardData ? event.clipboardData.getData('text/plain') : '';
            var rows = html ? parseHtmlTables(html) : [];
            if (!rows.length) {
                rows = parsePlain(text);
            }
            if (!rows.length) {
                return;
            }
            event.preventDefault();
            paste.value = rows.map(function (r) { return r.join('\t'); }).join('\n');
            parseRows(rows, statusBox, previewBox, importBtn);
        });

        importBtn.addEventListener('click', function () {
            if (!previewCards.length) {
                return;
            }
            importBtn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'portal_iv_import_cards');
            fd.append('nonce', cfg.nonce);
            fd.append('cards', JSON.stringify(previewCards));
            setStatus(statusBox, 'Создаю карточки…', false);
            postForm(fd).then(function (json) {
                if (!json || !json.success) {
                    importBtn.disabled = false;
                    setStatus(statusBox, (json && json.data && json.data.message) || 'Не удалось создать карточки.', true);
                    return;
                }
                var d = json.data || {};
                setStatus(statusBox, 'Готово. Создано: ' + (d.created || 0) + ', пропущено (уже есть): ' + (d.skipped || 0) + ', ошибок: ' + (d.errors || 0) + '. Страница будет обновлена.', false);
                window.setTimeout(function () {
                    window.location.reload();
                }, 900);
            }).catch(function () {
                importBtn.disabled = false;
                setStatus(statusBox, 'Ошибка сети при создании карточек.', true);
            });
        });

        closeBtn.addEventListener('click', closeOverlay);
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeOverlay();
            }
        });

        panel.appendChild(file);
        panel.appendChild(paste);
        panel.appendChild(statusBox);
        panel.appendChild(previewBox);
        panel.appendChild(actions);
        overlay.appendChild(panel);
        document.body.appendChild(overlay);
        paste.focus();
    }

    window.portalIvImportRun = openOverlay;
    console.log('Идеологическая вертикаль: выполните portalIvImportRun()');
})();
