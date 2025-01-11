CREATE SEQUENCE public.capture_company_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    MAXVALUE 9223372036854775807
    CACHE 1;

CREATE TABLE public."capture_company" (
	id bigint NOT NULL DEFAULT nextval('capture_company_id_seq'::regclass),
	body text NOT NULL,
	created_at timestamp NOT NULL,
	CONSTRAINT capture_company_pkey PRIMARY KEY (id)
);

CREATE INDEX capture_company_created_at_idx ON public.capture_company (created_at);