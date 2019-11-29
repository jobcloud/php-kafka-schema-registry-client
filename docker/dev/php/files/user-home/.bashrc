if ! pgrep ssh-agent > /dev/null
then
  eval `ssh-agent`
  rm -f ~/.ssh/ssh_auth_sock
  ln -sf "$SSH_AUTH_SOCK" ~/.ssh/ssh_auth_sock
fi
export SSH_AUTH_SOCK=~/.ssh/ssh_auth_sock
ssh-add -l > /dev/null || ssh-add
