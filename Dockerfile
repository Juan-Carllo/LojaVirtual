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

# Roda o PHP para criar o admin e depois inicia o Apache
CMD bash -c " \
    echo '⏳ Aguardando o banco...' && \
    until php -r 'try { new PDO(\"pgsql:host=db;port=5432;dbname=lojavirtual\", \"amigosdocasa\", \"senhasuperdificil\"); } catch (Exception \$e) { exit(1); }' ; do \
        sleep 1; \
    done && \
    echo '🚀 Executando criar_admin.php...' && \
    php /var/www/html/criar_admin.php && \
    echo '✅ Admin criado. Iniciando Apache...' && \
    apache2-foreground"
