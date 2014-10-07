---
title: Categoria Category One
permalink: /blog/category/category-one/
slug: category-one
---

{% for post in site.categories.category-one %}
  <article class="post">
    {% include post-header.html %}
  </article><!-- ./article -->
{% endfor %}
