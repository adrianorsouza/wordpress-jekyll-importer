---
title: Categoria Category Two
permalink: /blog/category/category-two/
slug: category-two
---

{% for post in site.categories.category-two %}
  <article class="post">
    {% include post-header.html %}
  </article><!-- ./article -->
{% endfor %}
