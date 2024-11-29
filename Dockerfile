# Use a imagem oficial do PHP com Apache como base
FROM php:8.3.4-apache

# Habilitar os módulos do Apache necessários
RUN a2enmod rewrite

# Configure o documento raiz do Apache para apontar para o diretório public
RUN sed -i 's!/var/www/html!/var/www/html/alura_test_studies/public!g' /etc/apache2/sites-available/000-default.conf

# Reinicie o Apache para aplicar as alterações
RUN service apache2 restart

# Instale as dependências do sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    curl \
    sudo

# Instale as extensões do PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Configure o local e o fuso horário
RUN echo "date.timezone = UTC" > /usr/local/etc/php/conf.d/timezone.ini

# Instale o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Defina o diretório de trabalho
WORKDIR /var/www/html/alura_test_studies

# Verifica se o arquivo composer.json existe, se não, copia um arquivo de exemplo
COPY composer.json /var/www/html/alura_test_studies/composer.json

# Instale as dependências do Composer
RUN composer install --no-interaction || true

# Definir permissões adequadas para o diretório
RUN chown -R www-data:www-data /var/www/html/alura_test_studies
RUN chmod -R 755 /var/www/html/alura_test_studies

# Remover o arquivo apache2.pid caso ele exista (resolver problema do PID)
RUN rm -f /var/run/apache2/apache2.pid

# Atualize o sistema e instale curl (para adicionar Node.js)
RUN apt-get update && apt-get install -y curl

# Adicione o Node.js 18 (inclui o npm)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Verifique as versões do Node.js e npm (opcional)
RUN node --version && npm --version

# Criar um usuário não-root para executar o processo
RUN useradd -ms /bin/bash appuser && \
    chown -R appuser:appuser /var/www/html/alura_test_studies

# Copiar o script de entrada para o contêiner
COPY docker-entrypoint.sh /usr/local/bin/

# Dar permissões ao script de entrada
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expor a porta 80 para o servidor Apache
EXPOSE 80

# Mudar para o usuário não-root
USER appuser

# Definir o script de entrada e iniciar o Apache
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
