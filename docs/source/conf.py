# Configuration file for the Sphinx documentation builder.

# -- Project information

project = 'broadway-sensitive-serializer'
copyright = '2022, Matteo Galacci'
author = 'Matteo Galacci'

release = '0.1'
version = '0.1.0'

# -- General configuration

extensions = [
    'sphinx.ext.duration',
    'sphinx.ext.doctest',
    'sphinx.ext.autodoc',
    'sphinx.ext.autosummary',
    'sphinx.ext.intersphinx',
    'sphinx.ext.autosectionlabel',
    'sphinx_tabs.tabs',
    'sphinxcontrib.phpdomain',
    'hoverxref.extension',
#     'sphinx.ext.viewcode',
#     'sphinx.ext.linkcode',
]

hoverxref_intersphinx = [
    "sphinx",
    "pip",
    "nbsphinx",
    "myst-nb",
    "ipywidgets",
    "jupytext",
]

hoverxref_auto_ref = True
hoverxref_domains = ["php"]
hoverxref_roles = [
    "option",
    "doc",  # Documentation pages
    "term",  # Glossary terms
]
hoverxref_role_types = {
    "mod": "modal",  # for Python Sphinx Domain
    "doc": "modal",  # for whole docs
    "class": "tooltip",  # for Python Sphinx Domain
    "ref": "tooltip",  # for hoverxref_auto_ref config
    "confval": "tooltip",  # for custom object
}

html_static_path = ["_static"]
html_js_files = ["js/expand_tabs.js"]

sphinx_tabs_valid_builders = ['linkcheck']

# Activate autosectionlabel plugin
autosectionlabel_prefix_document = True

intersphinx_disabled_domains = ['std']

templates_path = ['_templates']

source_suffix = ['.rst', '.md']

# The master toctree document.
master_doc = 'index'

# -- Options for HTML output

html_theme = 'sphinx_rtd_theme'

# -- Options for EPUB output
epub_show_urls = 'footnote'

#####################################
# PHP Syntax Highlighting in Sphinx #
#####################################
# load PhpLexer
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

# enable highlighting for PHP code not between <?php ... ?> by default
lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
primary_domain = "php"
#####################################
# PHP Syntax Highlighting in Sphinx #
#####################################

html_context = {
  "display_github": True,
  "github_user": "matiux",
  "github_repo": project,
  "github_version": "master",
  "conf_py_path": "/docs/source/",
  "source_suffix": source_suffix,
}

linkcheck_ignore = [
    r"http://127\.0\.0\.1",
    r"http://localhost",
    r"http://community\.dev\.readthedocs\.io",
    r"https://yourproject\.readthedocs\.io",
    r"https?://docs\.example\.com",
    r"https://foo\.readthedocs\.io/projects",
    r"https://github\.com.+?#L\d+",
    r"https://github\.com/readthedocs/readthedocs\.org/issues",
    r"https://github\.com/readthedocs/readthedocs\.org/pull",
    r"https://docs\.readthedocs\.io/\?rtd_search",
    r"https://readthedocs\.org/search",
    # This page is under login
    r"https://readthedocs\.org/accounts/gold",
]