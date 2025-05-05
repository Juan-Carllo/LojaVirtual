FROM php:8.2-apache

# Instala dependências e extensões necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

# Define a porta do Apache
EXPOSE 80

# Copia o código da aplicação
COPY ./src /var/www/html

# Define o arquivo index padrão
RUN echo 'DirectoryIndex index.php' >> /etc/apache2/apache2.conf
