/* helper for fetching and rendering markdown blog posts */

// create markdown-it parser once
const mdParser = window.markdownit ? window.markdownit() : { render: t => t };

async function fetchPostsJson() {
    const res = await fetch('posts.json');
    if (!res.ok) throw new Error('Unable to load posts.json');
    return res.json();
}

function slugToTitle(slug) {
    return slug
        .replace(/-/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

async function renderPost(name, container) {
    const md = await (await fetch(`posts/${name}.md`)).text();
    container.innerHTML = mdParser.render(md);
}

async function buildBlogList(container) {
    const posts = await fetchPostsJson();
    container.innerHTML = '';

    for (const item of posts) {
        const md = await (await fetch(`posts/${item.file}.md`)).text();
        // simple excerpt: first paragraph after header (work with CRLF)
        const parts = md.split(/\r?\n\r?\n/);
        let excerpt = '';
        if (parts.length > 1) {
            // choose the first non-empty segment after the header
            for (let i = 1; i < parts.length; i++) {
                if (parts[i].trim().length) {
                    excerpt = parts[i].trim();
                    break;
                }
            }
        }
        console.log('excerpt for', item.file, JSON.stringify(excerpt));

        const div = document.createElement('div');
        div.className = 'excerpt';
        div.innerHTML = `
            <h3><a href="blog.html?post=${item.file}">${item.title || slugToTitle(item.file)}</a></h3>
            ${mdParser.render(excerpt)}
            <p><a href="blog.html?post=${item.file}">Read More</a></p>
        `;
        container.appendChild(div);
    }
}

async function showBlogPage() {
    const params = new URLSearchParams(location.search);
    const container = document.getElementById('blog-container');
    if (params.has('post')) {
        await renderPost(params.get('post'), container);
    } else {
        await buildBlogList(container);
    }
}

async function showLatestExcerpts(container, count = 3) {
    const posts = await fetchPostsJson();
    const slice = posts.slice(0, count);
    for (const item of slice) {
        const md = await (await fetch(`posts/${item.file}.md`)).text();
        const parts = md.split(/\r?\n\r?\n/);
        let excerpt = '';
        if (parts.length > 1) {
            for (let i = 1; i < parts.length; i++) {
                if (parts[i].trim().length) {
                    excerpt = parts[i].trim();
                    break;
                }
            }
        }
        const div = document.createElement('div');
        div.className = 'excerpt';
        div.innerHTML = `
            <h3><a href="blog.html?post=${item.file}">${item.title || slugToTitle(item.file)}</a></h3>
            ${mdParser.render(excerpt)}
        `;
        console.log('rendered html', div.innerHTML);
        container.appendChild(div);
    }
}

// initialization based on body class
document.addEventListener('DOMContentLoaded', () => {
    if (document.body.classList.contains('blog-page')) {
        showBlogPage().catch(console.error);
    }
    if (document.body.classList.contains('index-page')) {
        const container = document.querySelector('.excerpt-list');
        if (container) showLatestExcerpts(container).catch(console.error);
    }
});
