/*
  # Agregar columna reservation_code a la tabla de reservas

  1. Cambios
    - Agregar columna reservation_code a la tabla booking_reservations
    - La columna es de tipo VARCHAR(20) y no puede ser nula
    - Se usa para almacenar el código único de cada reserva

  2. Notas
    - El código de reserva se genera en el formato RES-XXXXXXXX
    - Es importante para identificar cada reserva de forma única
*/

-- Agregar la columna reservation_code si no existe
DO $$ 
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns 
    WHERE table_name = 'booking_reservations' 
    AND column_name = 'reservation_code'
  ) THEN
    ALTER TABLE booking_reservations 
    ADD COLUMN reservation_code VARCHAR(20) NOT NULL;
  END IF;
END $$;