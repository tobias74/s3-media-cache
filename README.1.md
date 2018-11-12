example docker command:

docker run --name tobias-container -p 7000:80 -v "$PWD"/src:/var/www/html tobias



docker run --rm --interactive --tty     --volume $PWD:/app     composer --ignore-platform-reqs install




docker-compose -p stubist_live -f docker-compose.yml -f docker-compose.prod.yml  up --build

docker-compose -p remembrance_link_dev -f docker-compose.yml -f docker-compose.dev.yml  up --build
docker-compose -p remembrance_link_live -f docker-compose.yml -f docker-compose.prod.yml  up --build

docker-compose -p stubist_dev -f docker-compose.yml -f docker-compose.dev.yml  up -d --build



docker-compose -p stubist_live -f docker-compose.yml -f docker-compose.prod.yml  up --build > /var/log/mydockers/stubist_live.log &