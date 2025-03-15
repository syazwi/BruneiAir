-- This script was generated by the ERD tool in pgAdmin 4.
-- Please log an issue at https://github.com/pgadmin-org/pgadmin4/issues/new/choose if you find any bugs, including reproduction steps.
BEGIN;


CREATE TABLE IF NOT EXISTS public.aircraft
(
    aircraft_id serial NOT NULL,
    model character varying(50) COLLATE pg_catalog."default" NOT NULL,
    capacity integer NOT NULL,
    status character varying(20) COLLATE pg_catalog."default" DEFAULT 'available'::character varying,
    CONSTRAINT aircraft_pkey PRIMARY KEY (aircraft_id)
);

CREATE TABLE IF NOT EXISTS public.bookings
(
    booking_id serial NOT NULL,
    user_id integer NOT NULL,
    flight_id integer NOT NULL,
    seat_number character varying(10) COLLATE pg_catalog."default" NOT NULL,
    status character varying(20) COLLATE pg_catalog."default" DEFAULT 'confirmed'::character varying,
    booking_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT bookings_pkey PRIMARY KEY (booking_id)
);

CREATE TABLE IF NOT EXISTS public.crew_assignments
(
    assignment_id serial NOT NULL,
    flight_id integer NOT NULL,
    crew_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    role character varying(20) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT crew_assignments_pkey PRIMARY KEY (assignment_id)
);

CREATE TABLE IF NOT EXISTS public.flights
(
    flight_id serial NOT NULL,
    flight_number character varying(10) COLLATE pg_catalog."default" NOT NULL,
    departure timestamp without time zone NOT NULL,
    arrival timestamp without time zone NOT NULL,
    origin character varying(100) COLLATE pg_catalog."default" NOT NULL,
    destination character varying(100) COLLATE pg_catalog."default" NOT NULL,
    aircraft_id integer NOT NULL,
    status character varying(20) COLLATE pg_catalog."default" DEFAULT 'scheduled'::character varying,
    CONSTRAINT flights_pkey PRIMARY KEY (flight_id),
    CONSTRAINT flights_flight_number_key UNIQUE (flight_number)
);

CREATE TABLE IF NOT EXISTS public.users
(
    user_id serial NOT NULL,
    username character varying(50) COLLATE pg_catalog."default" NOT NULL,
    password_hash character varying(255) COLLATE pg_catalog."default" NOT NULL,
    role character varying(20) COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT users_pkey PRIMARY KEY (user_id),
    CONSTRAINT users_username_key UNIQUE (username)
);

ALTER TABLE IF EXISTS public.bookings
    ADD CONSTRAINT bookings_flight_id_fkey FOREIGN KEY (flight_id)
    REFERENCES public.flights (flight_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.bookings
    ADD CONSTRAINT bookings_user_id_fkey FOREIGN KEY (user_id)
    REFERENCES public.users (user_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.crew_assignments
    ADD CONSTRAINT crew_assignments_flight_id_fkey FOREIGN KEY (flight_id)
    REFERENCES public.flights (flight_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.flights
    ADD CONSTRAINT flights_aircraft_id_fkey FOREIGN KEY (aircraft_id)
    REFERENCES public.aircraft (aircraft_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

END;
