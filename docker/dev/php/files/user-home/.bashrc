alias cls='printf "\033c"'

export PS1='\[\e[1;32m\]\h\[\e[0m\] \[\e[1;37m\]\w\[\e[0m\] \[\e[1;32m\]\u\[\e[0m\] \[\e[1;37m\]\$\[\e[0m\] '

if [ -f ~/.bash_aliases ]; then
    . ~/.bash_aliases
fi

# autocomplete for `make` command
complete -W "\`grep -oE '^[a-zA-Z0-9_.-]+:([^=]|$)' ?akefile | sed 's/[^a-zA-Z0-9_.-]*$//'\`" make