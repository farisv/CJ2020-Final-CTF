package cyber.jawara.ctf;

import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.URLDecoder;
import java.nio.charset.StandardCharsets;
import java.util.Scanner;

import org.apache.velocity.Template;
import org.apache.velocity.context.Context;
import org.apache.velocity.runtime.RuntimeServices;
import org.apache.velocity.runtime.RuntimeSingleton;
import org.apache.velocity.runtime.parser.node.SimpleNode;
import org.apache.velocity.tools.view.VelocityViewServlet;

import javax.servlet.ServletConfig;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

public class NotFoundServlet extends VelocityViewServlet {
    private String templateString;

    @Override
    public void init(ServletConfig config) throws ServletException {
        super.init(config);

        InputStream stream = getServletContext().getResourceAsStream("templates/404.vm");
        InputStreamReader reader = new InputStreamReader(stream);
        try (Scanner scanner = new Scanner(stream).useDelimiter("\\A")) {
            templateString = scanner.hasNext() ? scanner.next() : "";
        }
    }

    @Override
    protected Template handleRequest(HttpServletRequest request, HttpServletResponse response, Context ctx) {
        Template template = null;
        try {
            String uri = (String)request.getAttribute("javax.servlet.forward.request_uri");
            String decoded = URLDecoder.decode(uri, StandardCharsets.UTF_8.toString())
                .replace("&", "&amp;")
                .replace("<", "&lt;")
                .replace(">", "&gt;")
                .replace("\"", "&quot;")
                .replaceAll("\\$\\w+", "")
                .replaceAll("\\#\\w+", "");

            String currentTemplate = String.format(templateString, "#[[" + decoded + "]]#");
            RuntimeServices runtimeServices = RuntimeSingleton.getRuntimeServices();
            SimpleNode node = runtimeServices.parse(currentTemplate, "404");

            template = new Template();
            template.setRuntimeServices(runtimeServices);
            template.setData(node);
            template.initDocument();
        } catch (Exception e) { }
        return template;
    }
}
