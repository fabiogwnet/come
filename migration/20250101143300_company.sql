CREATE TABLE public."company" (
	name varchar(250) NOT NULL,
	rank int8 NOT NULL,
	profit numeric(16, 3) NULL,
	created_at timestamp NOT null,
	capture_company_id int8 NOT null
);

CREATE INDEX company_search_name_idx ON public.company (name);
CREATE INDEX company_search_profit_idx ON public.company (profit);