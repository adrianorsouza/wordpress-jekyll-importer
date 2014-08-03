---
title: Categoria %1$s
description: Artigos relacionados a linguaguem de programacao %2$s
permalink: /blog/category/%2$s/
slug: %2$s
---

{%% for post in site.categories.%2$s %%}
  <article class="post">
    {%% include post-header.html %%}
  </article><!-- ./article -->
{%% endfor %%}
