# stash-ssh-keys

## Requirements

* Atlassian Stash 3.2 (might support earlier, but I haven't tested it)
* PHP 5.3

## Install

Copy `config.example.php` to `config.php` and update it with your database settings.

### Nginx Server Config

```
server {
    index index.php;
    listen 443 ssl;
    listen 80;
    root /srv/stash-ssh-keys;
    server_name  keys.dev.artfire.me;
    try_files $uri $uri/ /index.php;
    location ~ \.php$ {
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

## API

`/plain/:username` : authorized_keys format

`/json/:username` : [GitHub API response](https://developer.github.com/v3/users/keys/#list-public-keys-for-a-user) format

## Example Uses

### OpenSSH

Create the file `/usr/local/bin/openssh-stash-keys` with the following contents

#### /usr/local/bin/openssh-stash-keys
```
#!/bin/bash
curl -s https://keyserver.example.com/plain/$1
```

Set the following in `/etc/ssh/sshd_config`
#### /etc/ssh/sshd_config
```
AuthorizedKeysCommand /usr/local/bin/openssh-stash-keys
AuthorizedKeysCommandUser nobody
```

### CoreOS Cloud Config

```yaml
users:
  - name: jason
    groups:
      - docker
    coreos-ssh-import-url: https://keyserver.example.com/json/jasonrm
```

More info on CoreOS Cloud Config at [Using Cloud-Config](https://coreos.com/docs/cluster-management/setup/cloudinit-cloud-config/)

## Notes

Although I could switch between return types based on request headers, that feels like it'd be massive overkill for what should be really simple.
