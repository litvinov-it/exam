<?php

/**
 * Преобразует отформатированный номер телефона в числовой формат
 * @param string $phone Номер телефона в формате +7(XXX)-XXX-XX-XX
 * @return string Номер телефона в формате 79991234567
 */
function phoneToNumber($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

/**
 * Преобразует числовой формат номера телефона в отформатированный вид
 * @param string $number Номер телефона в формате 79991234567
 * @return string Номер телефона в формате +7(XXX)-XXX-XX-XX
 */
function numberToPhone($number) {
    // Убираем все нецифровые символы
    $number = preg_replace('/[^0-9]/', '', $number);
    
    // Если номер начинается с 8, заменяем на 7
    if (strlen($number) === 11 && $number[0] === '8') {
        $number = '7' . substr($number, 1);
    }
    
    // Форматируем номер
    if (strlen($number) === 11) {
        return sprintf(
            '+7(%s)-%s-%s-%s',
            substr($number, 1, 3),
            substr($number, 4, 3),
            substr($number, 7, 2),
            substr($number, 9, 2)
        );
    }
    
    return $number; // Возвращаем исходный номер, если формат не соответствует ожидаемому
} 