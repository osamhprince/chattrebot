FROM php:8.2-apache

# تغيير مجلد العمل إلى المجلد الرئيسي للموقع
WORKDIR /var/www/html

# نسخ جميع الملفات من المستودع إلى داخل السيرفر
COPY . .

# التأكد من أن Apache يمكنه الوصول للملفات
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# تشغيل Apache في الخلفية
CMD ["apache2-foreground"]
