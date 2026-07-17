<?php

namespace App\Services\CardGeneration;

use RuntimeException;

/**
 * Erreur de generation IA presentable a l'administrateur (timeout, quota
 * depasse, reponse invalide...). Le CDC 9.3 impose de gerer l'echec proprement
 * et de permettre une nouvelle tentative.
 */
class CardGenerationException extends RuntimeException
{
}
